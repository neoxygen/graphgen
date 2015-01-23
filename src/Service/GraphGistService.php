<?php

namespace Neoxygen\Graphgen\Service;

class GraphGistService
{
    public function createGist(array $graph)
    {
        $g = [
            'header' => null,
            'creator' => null,
            'reamin' => null
        ];
        $br = "\n";
        $gist = '';
        $gist .= '= Your Gist Title'."\n";
        $gist .= $br;
        $gist .= ':neo4j-version: 2.1.0'.$br;
        $gist .= ':author: Your Name'.$br;
        $gist .= ':twitter: @you'.$br;
        $gist .= ':tags: '.$br;
        $gist .= ':category: '.$br;
        $stats = $this->getStatistics($graph);
        $labels = implode(',', array_keys($stats));
        $gist .= ':labels: '.$labels.$br;
        $gist .= $br;
        $g['header'] = $gist;

        $remain = '=== Query some graph data'.$br;
        $nodeQueries = $this->createMatchNodeQueries($stats);
        foreach ($nodeQueries as $q) {
            $remain .= $q.$br;
        }
        $remain .= '[source,cypher]
----
MATCH (a)--(c)
RETURN a,c
LIMIT 50
----

And render as a table.

//table

Or Graph

//graph_result

=== Label Counts

[source,cypher]
----
MATCH (a)
UNWIND labels(a) as label
RETURN label,count(distinct a) as cnt
ORDER BY cnt DESC;
----

//table


=== Meta-Graph

[source,cypher]
----
MATCH (a)-[b]->(c)
UNWIND labels(a) as labelA
UNWIND labels(c) as labelC
RETURN labelA,type(b),labelC,count(distinct b) as cnt
ORDER BY cnt DESC;
----';
        $g['remain'] = $remain;

        return $g;
    }

    private function getStatistics(array $graph)
    {
        $stats = [];
        foreach ($graph['nodes'] as $node) {
            foreach ($node['labels'] as $label) {
                if (!array_key_exists($label, $stats)) {
                    $stats[$label] = 1;
                } else {
                    $stats[$label]++;
                }
            }
        }

        return $stats;
    }

    private function createMatchNodeQueries(array $stats)
    {
        arsort($stats);
        $qs = [];
        $qs[] = "\n";
        foreach ($stats as $nodeType => $count) {
            $q = '==== Retrieving '.$nodeType.' nodes'."\n";
            $q .= "\n";
            $q .= '[source,cypher]
            ----'."\n";
            $q .= 'MATCH (n:`'.$nodeType.'`)
            RETURN n'."\n";
            $q .= '----';
            $q .= "\n";
            $qs[] = $q;
        }

        return $qs;
    }
}