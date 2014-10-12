<?php

namespace Neoxygen\Graphgen\Service;

use Neoxygen\Graphgen\Service\Neo4jClient;

class GraphCounter
{
    protected $neo4j;

    protected $counterKey = 1;

    public function __construct(Neo4jClient $neo4jClient)
    {
        $this->neo4j = $neo4jClient;
    }

    public function incrementGraphGeneration()
    {
        $dt = new \DateTime("now");
        $now = $dt->getTimestamp();

        $q = 'MERGE (n:GraphCounter {counter_id = {props}.counterKey})
        ON MATCH SET n.count += 1
        ON CREATE SET n.count = 1';

        $params = ['counterKey' => $this->counterKey];

        $this->neo4j->getClient()->sendCypherQuery($q, $params);
    }
}