<?php

namespace Neoxygen\Graphgen\Service;

use Neoxygen\NeoClient\ClientBuilder;

class Neo4jClient
{
    protected $client;

    public function __construct($conn = 'default', $scheme = 'http', $host = 'localhost', $port = 7474)
    {
        $client = ClientBuilder::create()
            ->addDefaultLocalConnection()
            ->setAutoFormatResponse(true)
            ->build();

        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function sendQuery($query, array $params = array(), $conn = null, array $resultsDataContents = array('row', 'graph'))
    {
        return $this->client->sendCypherQuery($query, $params, $conn);
    }
}