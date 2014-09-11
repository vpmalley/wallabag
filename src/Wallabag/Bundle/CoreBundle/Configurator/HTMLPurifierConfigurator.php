<?php


namespace Wallabag\Bundle\CoreBundle\Configurator;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * Class HTMLPurifierConfigurator
 *
 * @Service("wallabag_core.html_purifier.configurator")
 *
 * @package Wallabag\Bundle\CoreBundle\Configurator
 */
class HTMLPurifierConfigurator {
    private $cacheDir;

    /**
     * @InjectParams({
     *      "cacheDir" = @Inject("%kernel.cache_dir%")
     * })
     * @param $cacheDir the application cache directory
     */
    function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }


    public function configure(\HTMLPurifier_Config $config) {
        $config->set('Cache.SerializerPath', $this->cacheDir);
        $config->set('HTML.SafeIframe', true);

        //allow YouTube, Vimeo and dailymotion videos
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|www\.dailymotion\.com/embed/video/)%');
    }
} 