<?php

namespace Wallabag\Bundle\CoreBundle\Document;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @MongoDB\Document
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;

    /**
     * @var string
     *
     * @MongoDB\String
     */
    private $apiToken;

    /**
     * @var string
     *
     * @MongoDB\String
     */
    private $feedToken;

    /**
     * @var string
     *
     * @MongoDB\String
     */
    private $authGoogleToken;

    /**
     * @var string
     *
     * @MongoDB\String
     */
    private $authMozillaToken;

    /**
     * @var Preference
     *
     * @MongoDB\EmbedOne(targetDocument="\Wallabag\Bundle\CoreBundle\Document\Preference")
     */
    private $preferences;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }

    /**
     * @return string
     */
    public function getAuthGoogleToken()
    {
        return $this->authGoogleToken;
    }

    /**
     * @param string $authGoogleToken
     */
    public function setAuthGoogleToken($authGoogleToken)
    {
        $this->authGoogleToken = $authGoogleToken;
    }

    /**
     * @return string
     */
    public function getAuthMozillaToken()
    {
        return $this->authMozillaToken;
    }

    /**
     * @param string $authMozillaToken
     */
    public function setAuthMozillaToken($authMozillaToken)
    {
        $this->authMozillaToken = $authMozillaToken;
    }

    /**
     * @return string
     */
    public function getFeedToken()
    {
        return $this->feedToken;
    }

    /**
     * @param string $feedToken
     */
    public function setFeedToken($feedToken)
    {
        $this->feedToken = $feedToken;
    }

    /**
     * Set preferences
     *
     * @param \Wallabag\Bundle\CoreBundle\Document\Preference $preferences
     * @return self
     */
    public function setPreferences(Preference $preferences)
    {
        $this->preferences = $preferences;
        return $this;
    }

    /**
     * Get preferences
     *
     * @return \Wallabag\Bundle\CoreBundle\Document\Preference $preferences
     */
    public function getPreferences()
    {
        return $this->preferences;
    }
}
