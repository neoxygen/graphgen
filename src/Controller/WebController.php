<?php

namespace Neoxygen\Graphgen\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\ResponseHeaderBag,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\Filesystem\Filesystem,
    Neoxygen\Neogen\Exception\SchemaException,
    Neoxygen\Neogen\Converter\GraphJSONConverter,
    Neoxygen\Neogen\Converter\StandardCypherConverter,
    Neoxygen\Neogen\Converter\CypherStatementsConverter;
use Michelf\MarkdownExtra;

class WebController
{
    public function home(Application $application, Request $request)
    {
        $root = $application['root_dir'];
        $file = $root.'/counter';

        $current = $this->getCounter($file);

        return $application['twig']->render('base.html.twig', array('current' => $current));
    }

    public function docAction(Application $application, Request $request)
    {
        $file = __DIR__.'/../../docs/00_introduction.md';
        $contents = file_get_contents($file);

        $doc = MarkdownExtra::defaultTransform($contents);

        $root = $application['root_dir'];
        $file = $root.'/counter';

        $current = $this->getCounter($file);

        return $application['twig']->render('doc.html.twig', array('doctext' => $doc, 'current' => $current));


    }

    public function transformPattern(Application $application, Request $request)
    {
        $root = $application['root_dir'];
        $file = $root.'/counter';

        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');
        $response = new JsonResponse();

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new GraphJSONConverter();
            $graphJson = $converter->convert($graph);
            $response->setData($graphJson);
            $response->setStatusCode(200);
            $this->increment($file);
            $stats = $application['stats'];
            $stats->addUserGenerateAction($request->getClientIp(), $pattern);
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
            $stats = $application['stats'];
            $stats->addUserGenerateAction($request->getClientIp(), $pattern);
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

    public function getPopulateQueries(Application $application, Request $request)
    {
        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new CypherStatementsConverter();
            $converter->convert($graph);
            $sts = $converter->getStatements();
            $response = new JsonResponse();
            $data = json_encode($sts);
            $response->setData($data);
            return $response;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function increment($file)
    {
        $fs = new Filesystem();
        if (!$fs->exists($file)){
            $fs->touch($file);
            $fs->chmod($file, 0777);
            file_put_contents($file, 1);
            return true;
        }
        $fs->chmod($file, 0777);
        $current = (int) file_get_contents($file);
        $current++;
        file_put_contents($file, $current);
        return true;

    }

    private function getCounter($file)
    {
        if (!file_exists($file)){
            return 0;
        }
        $current = (int) file_get_contents($file);

        return $current;
    }
}