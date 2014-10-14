<?php

namespace Neoxygen\Graphgen\Service;

class UrlShortenerService
{
    public function getShortUrl($text)
    {
        return crc32(microtime(true) . $text);
    }
}