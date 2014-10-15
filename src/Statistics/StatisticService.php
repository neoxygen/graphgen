<?php

namespace Neoxygen\Graphgen\Statistics;

use GuzzleHttp\Url;
use Neoxygen\Graphgen\Service\Neo4jClient,
    Neoxygen\Graphgen\Service\UrlShortenerService,
    Neoxygen\Graphgen\Statistics\CypherQueryRepository,
    Neoxygen\NeoClient\Formatter\ResponseFormatter;
use Neoxygen\NeoClient\Formatter\Node;

class StatisticService
{
    protected $neoService;

    protected $cypher;

    protected $formatter;

    protected $urlService;

    public function __construct(Neo4jClient $client, UrlShortenerService $urlService)
    {
        $this->neoService = $client;
        $this->cypher = new CypherQueryRepository();
        $this->formatter = new ResponseFormatter();
        $this->urlService = new UrlShortenerService();
    }

    public function addUserGenerateAction($clientIp, $pattern)
    {
        $url = $this->urlService->getShortUrl();

        $q = 'CREATE (u:UserRequest)-[r:GENERATE_PATTERN]->(p:Pattern)
        SET u.ip = {ip}, p.pattern = {pattern}, p.url = {url}, r.generated_on = timestamp()';

        $p = [
            'ip' => $clientIp,
            'pattern' => addslashes($pattern),
            'url' => addslashes($url)
        ];

        $this->neoService->sendQuery($q, $p);


        $query = 'MERGE (n:GenerationCounter {id: {props}.counterKey})
        ON MATCH SET n.count = n.count +1
        ON CREATE SET n.count = 1;';

        $params = [
            'props' => [
                'counterKey' => 1
            ]
        ];

        $this->neoService->sendQuery($query, $params);

        return $url;
    }

    public function getPatternFromUrl($url)
    {
        $url = addslashes($url);
        $params = ['url' => $url];

        $q = 'MATCH p=(pattern:Pattern) WHERE pattern.url = {url} RETURN pattern';
        $response = $this->neoService->sendQuery($q, $params);
        $formatter = new ResponseFormatter();
        $result = $formatter->format($response);
        if (null === $result->getSingleNode('Pattern')){
            return false;
        } else {
            return $result->getSingleNode('Pattern')->getProperty('pattern');
        }

    }

    public function getGenerationCount()
    {
        $q = 'MATCH p=(n:GenerationCounter) WHERE n.id = 1 RETURN n;';
        $response = $this->neoService->sendQuery($q);

        $formatter = new ResponseFormatter();
        $result = $formatter->format($response);
        $counter = $result->getSingleNode();

        if (!$counter instanceof Node){
            return 0;
        }
        $count = $counter->getProperty('count');

        return $count;
    }
}