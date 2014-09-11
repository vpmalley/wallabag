<?php


namespace Wallabag\Bundle\CoreBundle\Url;


interface UrlFetcher {
    /**
     * @param string $url the url to fetch
     * @return Url extracted content
     */
    public function fetch($url);
} 