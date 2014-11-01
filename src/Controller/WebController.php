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
    Neoxygen\Neoclient\Exception\HttpException,
    Neoxygen\NeoClient\Connection\Connection,
    Neoxygen\ConsoleClient\Client as ConsoleClient;
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

    public function docAction(Application $application, Request $request, $part)
    {
        $file = __DIR__.'/../../docs/'.$part.'.md';
        if (!file_exists($file)){
            $application->abort('404', 'The page you asked can not be found');
        }
        $contents = file_get_contents($file);

        $doc = MarkdownExtra::defaultTransform($contents);
        $current = $this->getCounter($application);

        return $application['twig']->render('doc.html.twig', array('doctext' => $doc, 'current' => $current));


    }

    public function supportAction(Application $application, Request $request)
    {
        $file = __DIR__.'/../../docs/support.md';
        $contents = file_get_contents($file);

        $doc = MarkdownExtra::defaultTransform($contents);
        $current = $this->getCounter($application);

        return $application['twig']->render('support.html.twig', array('doctext' => $doc, 'current' => $current));


    }

    public function populateExternal(Application $application, Request $request)
    {
        $host = $request->request->get('host');
        $user = $request->request->get('user');
        $password = $request->request->get('password');
        $data = json_decode($request->request->get('data'), true);
        $doEmpty = $request->request->get('doEmpty');
        $neo = $application['neo4j'];
        $client = $neo->getClient();
        $info = parse_url($host);
        $scheme = $info['scheme'];
        $hostx = $info['host'];
        if (isset($info['port'])){
            $port = $info['port'];
        } elseif ($scheme == 'http'){
            $port = 7474;
        } elseif ($scheme == 'https'){
            $port = 7473;
        } else {
            $port = 7474;
        }

        $conn = new Connection($host, $scheme, $hostx, $port, true, $user, $password);

        $client->getConnectionManager()->registerConnection($conn);

        if ($doEmpty){
            $q = 'MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n';
            $neo->getClient()->sendCypherQuery($q, array(), $host);
        }
        foreach ($data['constraints'] as $constraint){
            try {
                $neo->getClient()->sendCypherQuery($constraint['statement'], array(), $host);
            } catch (HttpException $e){

            }

        }

        foreach ($data['nodes'] as $node){
            $neo->getClient()->sendCypherQuery($node['statement'], $node['parameters'], $host);
        }

        foreach ($data['edges'] as $edge){
            if (!isset($edge['parameters'])){
                $params = array();
            } else {
                $params = $edge['parameters'];
            }
            $neo->getClient()->sendCypherQuery($edge['statement'], $params, $host);
        }
        $response = new JsonResponse();
        $response->setStatusCode(200);

        return $response;
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

    public function precalculateAction(Application $application, Request $request)
    {
        $pattern = $request->request->get('pattern');
        $response = new JsonResponse();

        try {
            $graph = $application['converter']->precalculateGraph($pattern);
            $response->setData(json_encode($graph));
            $response->setStatusCode(200);
        } catch (SchemaException $e){
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

    public function createConsoleAction(Application $application, Request $request)
    {
        $pattern = json_decode($request->request->get('pattern'), true);
        $response = new JsonResponse();
        try {
            $converter = $application['converter'];
            $statements = $converter->transformGraphJsonToStandardCypher($pattern);
            $consoleClient = new ConsoleClient();
            foreach ($statements as $statement){
                $consoleClient->addInitQuery($statement);
            }
            $consoleClient->addConsoleMessage('Welcome to your graph generated from Graphgen');
            $consoleClient->addConsoleQuery('MATCH (n) RETURN n LIMIT 5');
            $consoleClient->createConsole();
            $consoleUrl = $consoleClient->getShortLink();
            $body = array(
                'console_url' => $consoleUrl
            );
            $response->setData($body);
        } catch (SchemaException $e){
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
        //print_r(json_last_error_msg());
        //$pattern = \GuzzleHttp\json_decode($request->request->get('pattern'));
        //print_r($pattern);
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
(person:Person {firstname: firstName, lastname: lastName } *20)-[:KNOWS *n..n]->(person)
(person)-[:HAS *n..n]->(skill:Skill {name: progLanguage} *15)
(company:Company {name: company, desc: catchPhrase} *10)-[:LOOKS_FOR_COMPETENCE *n..n]->(skill)
(company)-[:LOCATED_IN *n..1]->(country:Country {name: country} *25)
(person)-[:LIVES_IN *n..1]->(country)';

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