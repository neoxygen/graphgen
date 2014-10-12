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
use GuzzleHttp\Client;

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
        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');
        $response = new JsonResponse();

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new GraphJSONConverter();
            $graphJson = $converter->convert($graph);
            $response->setData($graphJson);
            $response->setStatusCode(200);
            $this->increment($application, $request, $pattern);
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

    public function getConsoleLinkAction(Application $application, Request $request)
    {
        $gen = $application['neogen'];
        $pattern = $request->request->get('pattern');

        try {
            $graph = $gen->generateGraphFromCypher($pattern);
            $converter = new StandardCypherConverter();
            $converter->convert($graph);
            $sts = $converter->getStatements();
            $q = '';
            foreach ($sts as $statement) {
                $q .= $statement."\n";
            }
            $bodyContents = [
                'init' => $q,
                'query' => 'MATCH (n) RETURN n LIMIT 25;'
            ];
            $body = json_encode($bodyContents);
            $client = new Client();
            $request = $client->createRequest('POST', 'http://console.neo4j.org/r/share', ['body' => $body]);
            $request->setHeader('Content-Type', 'application/json');

            $response = $client->send($request);
            $shareLink = trim((string) $response->getBody());

            $consoleLink = 'http://console.neo4j.org/r/'.$shareLink;

            return $application->redirect($consoleLink);
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

    private function increment(Application $application, Request $request, $pattern)
    {
        $stats = $application['stats'];
        $stats->addUserGenerateAction($request->getClientIp(), $pattern);
    }

    private function getCounter(Application $application)
    {
        $stat = $application['stats'];
        $current = $stat->getGenerationCount();

        return $current;
    }
}