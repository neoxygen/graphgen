<?php

namespace Neoxygen\Graphgen\Service;

use Neoxygen\NeoClient\Client;

class Neo4jClient
{
    protected $client;

    public function __construct($conn = 'default', $scheme = 'http', $host = 'localhost', $port = 7474)
    {
        $client = new Client();
        $client->addConnection($conn, $scheme, $host, $port)
            ->build();

        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function sendQuery($query, array $params = array(), $conn = null, array $resultsDataContents = array('row', 'graph'))
    {
        return $this->client->sendCypherQuery($query, $params, $conn, $resultsDataContents);
    }
}