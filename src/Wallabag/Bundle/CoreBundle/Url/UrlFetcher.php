<?php


namespace Wallabag\Bundle\CoreBundle\Url;


interface UrlFetcher {
    public function fetch($url, $max, $links);
} 