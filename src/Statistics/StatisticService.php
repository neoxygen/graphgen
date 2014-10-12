<?php

namespace Neoxygen\Graphgen\Statistics;

use Neoxygen\Graphgen\Service\Neo4jClient,
    Neoxygen\Graphgen\Statistics\CypherQueryRepository,
    Neoxygen\NeoClient\Formatter\ResponseFormatter;
use Neoxygen\NeoClient\Formatter\Node;

class StatisticService
{
    protected $neoService;

    protected $cypher;

    protected $formatter;

    public function __construct(Neo4jClient $client)
    {
        $this->neoService = $client;
        $this->cypher = new CypherQueryRepository();
        $this->formatter = new ResponseFormatter();
    }

    public function addUserGenerateAction($clientIp, $pattern)
    {
        /**
        $blacklist = array('localhost', '127.0.0.1', '0.0.0.0');
        if (in_array($clientIp, $blacklist)){
            return;
        }*/

        $q = 'CREATE (u:UserRequest)-[r:GENERATE_PATTERN]->(p:Pattern)
        SET u.ip = {ip}, p.pattern = {pattern}, r.generated_on = timestamp()';

        $p = [
            'ip' => $clientIp,
            'pattern' => addslashes($pattern)
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