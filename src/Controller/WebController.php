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
    protected $application;

    public function home(Application $application, Request $request)
    {
        $this->application = $application;

        $graph = $request->query->get('graph');

        $pattern = $this->getPattern($graph);

        $current = $this->getCounter($application);

        return $application['twig']->render('base.html.twig', array('current' => $current, 'pattern' => $pattern));
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
            $graph = json_decode($graphJson, true);
            $url = $this->increment($application, $request, $pattern);
            $graph['shareUrl'] = urlencode($url);
            $response->setData(json_encode($graph));
            $response->setStatusCode(200);
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
            $url = $stats->addUserGenerateAction($request->getClientIp(), $pattern);
            return $url;
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

    private function getPattern($url = null)
    {
        $p = '// Example :
(p:Person {firstname: firstName, lastname: lastName } *35)-[:KNOWS *n..n]->(p)
(p)-[:HAS *n..n]->(s:Skill {name: progLanguage} *20)
(c:Company {name: company, desc: catchPhrase} *20)-[:LOOKS_FOR_COMPETENCE *n..n]->(s)
(c)-[:LOCATED_IN *n..1]->(country:Country {name: country} *70)
(p)-[:LIVES_IN *n..1]->(country)';

        if (null !== $url){
            $pattern = $this->application['stats']->getPatternFromUrl($url);
            if (false !== $pattern){
                return $pattern;
            } else {
                $this->application['session']->getFlashBag()->add('message', 'Unable to find a pattern for the key "'.$url.'".
                Loading default pattern example.');
            }
        }

        return $p;
    }
}