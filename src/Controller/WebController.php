<?php

namespace Neoxygen\Graphgen\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\ResponseHeaderBag,
    Symfony\Component\HttpFoundation\JsonResponse,
    Neoxygen\Neogen\Exception\SchemaException,
    Neoxygen\Neogen\Converter\GraphJSONConverter,
    Neoxygen\Neogen\Converter\StandardCypherConverter,
    Neoxygen\Neogen\Converter\CypherStatementsConverter;

class WebController
{
    public function home(Application $application, Request $request)
    {
        $current = 0;
        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            $fetch = apc_fetch('graphgen_generated');
            if (null !== $fetch) {
                $current = $fetch;
            }
        }

        return $application['twig']->render('base.html.twig', array('current' => $current));
    }

    public function transformPattern(Application $application, Request $request)
    {
        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');
        $response = new JsonResponse();

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new GraphJSONConverter();
            $graphJson = $converter->convert($graph);
            $response->setData($graphJson);
            $response->setStatusCode(200);
            $this->increment();
        } catch (SchemaException $e) {
            $data = array(
                'error' => array(
                    'code' => 320,
                    'message' => $e->getMessage()
                )
            );
            $response->setData($data);
            $response->setStatusCode(320);
        }

        return $response;

    }

    public function exportToGraphJson(Application $application, Request $request)
    {
        $file = sys_get_temp_dir().'/'.uniqid().'.json';
        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new GraphJSONConverter();
            $graphJson = $converter->convert($graph);
            file_put_contents($file, $graphJson);
            return $application
                ->sendFile($file)
                ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'export.json');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function exportToCypher(Application $application, Request $request)
    {
        $file = sys_get_temp_dir().'/'.uniqid().'.json';
        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new StandardCypherConverter();
            $converter->convert($graph);
            $sts = $converter->getStatements();
            $text = '';
            foreach ($sts as $statement) {
                $text .= $statement."\n";
            }
            file_put_contents($file, $text);
            return $application
                ->sendFile($file)
                ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'cypher_export.cql');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function increment()
    {
        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            $current = apc_fetch('graphgen_generated');
            apc_store('graphgen_generated', $current++);
        }
    }
}