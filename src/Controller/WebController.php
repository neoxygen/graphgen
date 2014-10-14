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
    Neoxygen\Neogen\Converter\CypherStatementsConverter,
    Neoxygen\Neoclient\Exception\HttpException;
use Michelf\MarkdownExtra;

class WebController
{
    public function home(Application $application, Request $request)
    {
        $current = $this->getCounter($application);

        return $application['twig']->render('base.html.twig', array('current' => $current));
    }

    public function docAction(Application $application, Request $request)
    {
        $file = __DIR__.'/../../docs/00_introduction.md';
        $contents = file_get_contents($file);

        $doc = MarkdownExtra::defaultTransform($contents);
        $current = $this->getCounter($application);

        return $application['twig']->render('doc.html.twig', array('doctext' => $doc, 'current' => $current));


    }

    public function transformPattern(Application $application, Request $request)
    {
        $pattern = $request->request->get('pattern');
        $response = new JsonResponse();

        try {
            $graphJson = $application['converter']->transformPatternToGraphJson($pattern);

            $response->setData($graphJson);
            $response->setStatusCode(200);
            $this->increment($application, $request, $pattern);
            $application['monolog']->addDebug('Pattern transformed successfully');
        } catch (SchemaException $e) {
            $data = array(
                'error' => array(
                    'code' => 320,
                    'message' => $e->getMessage()
                )
            );
            $response->setData($data);
            $response->setStatusCode(320);
            $application['monolog']->addWarning(sprintf('Unable to transform pattern "%s"', $e->getMessage()));
        }

        return $response;
    }

    public function exportToGraphJson(Application $application, Request $request)
    {
        $file = sys_get_temp_dir().'/'.uniqid().'.json';
        $graph = $request->request->get('pattern');

        try {
            file_put_contents($file, $graph);
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
        $pattern = json_decode($request->request->get('pattern'), true);

        try {
            $converter = $application['converter'];
            $statements = $converter->transformGraphJsonToStandardCypher($pattern);
            $text = '';
            foreach ($statements as $statement) {
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
        $pattern = json_decode($request->request->get('pattern'), true);

        try {
            $statements = $application['converter']->transformGraphJsonToCypherPopulate($pattern);
            $response = new JsonResponse();
            $data = json_encode($statements);
            $response->setData($data);
            return $response;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function increment(Application $application, Request $request, $pattern)
    {
        try {
            $stats = $application['stats'];
            $stats->addUserGenerateAction($request->getClientIp(), $pattern);
            return;
        } catch (HttpException $e){
            return;
        }

    }

    private function getCounter(Application $application)
    {
        try {
            $stat = $application['stats'];
            $current = $stat->getGenerationCount();

            return $current;
        } catch (HttpException $e){
            $application['monolog']->addCritical(sprintf('Neo4j connection error %s', $e->getMessage()));
            return 0;
        }
    }
}