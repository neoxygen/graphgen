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
        $gist .= '= CHANGE THIS TO YOUR GIST TITLE'."\n";
        $gist .= $br;
        $gist .= ':neo4j-version: 2.1.0'.$br;
        $gist .= ':author: Your Name'.$br;
        $gist .= ':twitter: @you'.$br;
        $gist .= ':tags: TODO'.$br;
        $gist .= ':category: TODO'.$br;
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
        $propQueries = $this->matchOnPropertyQueries($graph, $stats);
        foreach ($propQueries as $q) {
            $remain .= $q.$br;
        }
        $relQ = $this->matchRelationshipsQueries($graph);
        foreach ($relQ as $q) {
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
        $lr = 'table';
        foreach ($stats as $nodeType => $count) {
            $q = '==== Retrieving '.$nodeType.' nodes'."\n";
            $q .= "\n";
            $q .= '[source,cypher]
            ----'."\n";
            $q .= 'MATCH (n:`'.$nodeType.'`)
            RETURN --CHANGE ME--
            LIMIT 10'."\n";
            $q .= '----';
            $q .= "\n";
            $q .= '//'.$lr."\n";
            if ($lr == 'table') {
                $lr = 'graph';
            } else {
                $lr = 'table';
            }
            $qs[] = $q;
        }

        return $qs;
    }

    private function matchOnPropertyQueries(array $graph, $stats)
    {
        arsort($stats);
        $qs = [];
        $used = [];
        foreach ($stats as $label => $count) {
            foreach ($graph['nodes'] as $node) {
                if (in_array($label, $node['labels']) && !array_key_exists($label, $used)) {
                    if (isset($node['properties']) && !empty($node['properties'])) {
                        foreach ($node['properties'] as $k => $v) {
                            if (is_int($v)) {
                                $value = $v;
                            } else {
                                $value = '"'.$v.'"';
                            }
                            $q = '==== Retrieving a '.$label.' by his '.$k."\n";
                            $q .= '[source,cypher]
                            ----'."\n";
                            $q .= 'MATCH (n:`'.$label.'`)
                            WHERE n.'.$k.' = '.$value.'
                            RETURN -- CHOOSE WHAT TO RETURN --
                            LIMIT 5'."\n";
                            $q .= '----'."\n";
                            $q .= '//table'."\n";
                            $used[$label] = null;
                            $qs[] = $q;
                            break;
                        }
                    }
                }
            }
        }

        return $qs;
    }

    private function matchRelationshipsQueries($graph)
    {
        $qs = [];
        $used = [];
        $lr = 'table';
        foreach ($graph['edges'] as $edge) {
            if (!array_key_exists($edge['type'], $used)) {
                $source = $edge['source_label'];
                $target = $edge['target_label'];
                $q = '==== Finding '.$edge['type'].' relationships'."\n";
                $q .= '[source,cypher]
                ----'."\n";
                $q .= 'MATCH (a)-[r:`'.$edge['type'].'`]->(b)
                RETURN a,r,b
                LIMIT 10'."\n";
                $q .= '----'."\n";
                $q .= '//'.$lr."\n";
                $used[$edge['type']] = null;
                $qs[] = $q;
                if ($lr == 'table') {
                    $lr = 'graph';
                } else {
                    $lr = 'table';
                }
            }
        }

        return $qs;
    }
}