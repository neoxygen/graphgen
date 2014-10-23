<?php

namespace Neoxygen\Graphgen\Service;

use Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Graph\Graph,
    Neoxygen\Neogen\Converter\GraphJSONConverter,
    Neoxygen\Neogen\Converter\StandardCypherConverter,
    Neoxygen\Neogen\Converter\CypherStatementsConverter;

class ConverterService
{
    private $generator;

    private $graphJsonConverter;

    private $standardCypherConverter;

    private $cypherPopulateConverter;

    public function __construct(Neogen $generator)
    {
        $this->generator = $generator;
        $this->graphJsonConverter = new GraphJSONConverter();
        $this->standardCypherConverter = new StandardCypherConverter();
        $this->cypherPopulateConverter = new CypherStatementsConverter();
    }

    public function transformPatternToGraphJson($pattern)
    {
        $schema = $this->generator->generateGraphFromCypher($pattern);

        return $this->graphJsonConverter->convert($schema);
    }

    public function transformGraphJsonToStandardCypher(array $schema)
    {
        $graph = new Graph();

        foreach ($schema['nodes'] as $node)
        {
            $graph->setNode($node);
        }

        foreach ($schema['edges'] as $edge)
        {
            $edge['target'] = $edge['_target'];
            $edge['source'] = $edge['_source'];
            $graph->setEdge($edge);
        }

        $cypher = $this->standardCypherConverter->convert($graph);

        return $cypher;
    }

    public function precalculateGraph($pattern)
    {
        return $this->generator->generateGraphFromCypher($pattern, true);
    }

    public function transformGraphJsonToCypherPopulate(array $schema)
    {
        $graph = new Graph();

        foreach ($schema['nodes'] as $node)
        {
            $node['neogen_id'] = $node['_id'];
            $graph->setNode($node);
        }

        foreach ($schema['edges'] as $edge)
        {
            $edge['target'] = $edge['_target'];
            $edge['source'] = $edge['_source'];
            $graph->setEdge($edge);
        }

        return $this->cypherPopulateConverter->convert($graph);
    }
}