<?php


namespace Wallabag\Bundle\FullTextRssBundle\UrlFetcher;


use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Psr\Log\LoggerInterface;
use Wallabag\Bundle\CoreBundle\Url\Url;
use Wallabag\Bundle\CoreBundle\Url\UrlFetcher;

/**
 * Class FullTextRssUrlFetcher
 * @Service("full_text_rss_url_fetcher")
 * @Tag("wallabag.url_fetcher")
 *
 * @package Wallabag\Bundle\FullTextRssBundle\UrlFetcher
 */
class FullTextRssUrlFetcher implements UrlFetcher {
    const DEFAULT_MAX = 5;
    const DEFAULT_LINK_STRATEGY = "preserve";
    const DEFAULT_FORMAT = "json";
    const DEFAULT_SUBMIT = "Create Feed";

    /**
     * @var string the application root directory
     */
    private $rootDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @InjectParams({
     *      "logger" = @Inject("logger"),
     *      "rootDir" = @Inject("%kernel.root_dir%")
     * })
     * @param string $rootDir the application root directory
     */
    function __construct($logger, $rootDir)
    {
        $this->logger = $logger;
        $this->rootDir = $rootDir;
    }

    public function fetch($url)
    {
        // Saving and clearing context
        $REAL = array();
        foreach( $GLOBALS as $key => $value ) {
            if( $key != 'GLOBALS' && $key != '_SESSION' && $key != 'HTTP_SESSION_VARS' ) {
                $GLOBALS[$key]  = array();
                $REAL[$key]     = $value;
            }
        }
        // Saving and clearing session
        if (isset($_SESSION)) {
            $REAL_SESSION = array();
            foreach( $_SESSION as $key => $value ) {
                $REAL_SESSION[$key] = $value;
                unset($_SESSION[$key]);
            }
        }

        // Running code in different context
        $scope = function() {
            global $extractor, $http;
            extract( func_get_arg(1) );
            $_GET = $_REQUEST = array(
                "url" => $url,
                "max" => FullTextRssUrlFetcher::DEFAULT_MAX,
                "links" => FullTextRssUrlFetcher::DEFAULT_LINK_STRATEGY,
                "exc" => "",
                "format" => FullTextRssUrlFetcher::DEFAULT_FORMAT,
                "submit" => FullTextRssUrlFetcher::DEFAULT_SUBMIT
            );
            ob_start();
            set_error_handler(function($errno, $errstr) {$this->logger->info("Full Text RSS - {errno} : {errstr}", array("errno" => $errno, "errstr" => $errstr));});
            require func_get_arg(0);
            $json = ob_get_contents();
            ob_end_clean();
            restore_error_handler();
            return $json;
        };

        $json = $scope($this->rootDir."/../vendor/fivefilters/full-text-rss/makefulltextfeed.php", array("url" => $url));

        // Clearing and restoring context
        foreach ($GLOBALS as $key => $value) {
            if($key != "GLOBALS" && $key != "_SESSION" ) {
                unset($GLOBALS[$key]);
            }
        }
        foreach ($REAL as $key => $value) {
            $GLOBALS[$key] = $value;
        }

        // Clearing and restoring session
        if (isset($REAL_SESSION)) {
            foreach($_SESSION as $key => $value) {
                unset($_SESSION[$key]);
            }

            foreach($REAL_SESSION as $key => $value) {
                $_SESSION[$key] = $value;
            }
        }

        $content = json_decode($json, true);
        $content = $content['rss']['channel']['item'];
        return new Url($content['description'], $content['title'], $url);
    }
}