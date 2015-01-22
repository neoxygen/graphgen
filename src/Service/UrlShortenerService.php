<?php

namespace Neoxygen\Graphgen\Service;

use Hashids\Hashids;

class UrlShortenerService
{
    private $hasher;

    public function __construct()
    {
        $this->hasher = new Hashids();
    }

    public function getShortUrl()
    {
        $time = new \DateTime("now");
        $timestamp = $time->getTimestamp();
        $id = rand(1, 10000);
        $nid = rand(1,20);

        $code = strtolower($this->hasher->encode($nid, $id));

        return $code;
    }
}