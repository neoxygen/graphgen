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
        $id = crc32(uniqid());

        $code = $this->hasher->encode($timestamp, $id);

        return $code;
    }
}