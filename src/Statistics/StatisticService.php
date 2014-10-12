<?php

namespace Neoxygen\Graphgen\Statistics;

use Neoxygen\Graphgen\Service\Neo4jClient,
    Neoxygen\Graphgen\Statistics\CypherQueryRepository,
    Neoxygen\NeoClient\Formatter\ResponseFormatter;

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
    }

    public function getTotalGraphs()
    {
        $q = 'MATCH p=(u:UserRequest)-[:GENERATE_PATTERN]->(pattern:Pattern) RETURN p';
        $response = $this->neoService->sendQuery($q);
        $result = $this->formatter->format($response);

        $total = $result->getRelationshipsCount();

        return $total;
    }
}