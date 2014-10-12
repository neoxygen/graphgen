<?php

namespace Neoxygen\Graphgen\Tests\Statistics;

use Neoxygen\Graphgen\Service\Neo4jClient,
    Neoxygen\Graphgen\Statistics\StatisticService,
    Neoxygen\NeoClient\Formatter\ResponseFormatter,
    Neoxygen\NeoClient\Formatter\Node;
use Faker\Factory;

class StatisticServiceTest extends \PHPUnit_Framework_TestCase
{
    private $faker;

    public function testUserRequestIsAdded()
    {
        $pattern = '(p:Person *35)';
        $ip = $this->getIp();
        $service = $this->buildService();

        $service->addUserGenerateAction($ip, $pattern);
        $this->matchUserRequest($ip, $pattern);

        $this->assertTrue($service->getTotalGraphs() > 0);
    }

    private function buildService()
    {
        $client = new Neo4jClient();
        $service = new StatisticService($client);

        return $service;
    }

    private function getIp()
    {
        if (null === $this->faker){
            $this->faker = Factory::create();
        }

        return $this->faker->ipv4;
    }

    private function matchUserRequest($ip, $pattern)
    {
        $client = new Neo4jClient();
        $formatter = new ResponseFormatter();
        $q = 'MATCH p=(u:UserRequest {ip: \''.$ip.'\'})-[:GENERATE_PATTERN]->(pattern:Pattern) RETURN p';

        $response = $client->sendQuery($q);
        $result = $formatter->format($response);
        $userRequest = $result->getSingleNode('UserRequest');
        $this->assertNotNull($userRequest);
        $this->assertTrue($userRequest instanceof Node);
        if ($userRequest instanceof Node){
            $this->assertEquals($userRequest->getProperty('ip'), $ip);
            $saved_pattern = $userRequest->getSingleRelationship()->getEndNode()->getProperty('pattern');
            $this->assertEquals($saved_pattern, $pattern);
        }

    }
}