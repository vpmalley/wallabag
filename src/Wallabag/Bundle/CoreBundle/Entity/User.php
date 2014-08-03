<?php

namespace Wallabag\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="preferred_locale", type="string", length=5)
     */
    private $preferredLocale;

    /**
     * @var integer
     *
     * @ORM\Column(name="items_per_page", type="integer")
     */
    private $itemsPerPage;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string")
     */
    private $theme = 'original';

    /**
     * @var string
     *
     * @ORM\Column(name="items_sorting_direction", type="string")
     */
    private $itemsSortingDirection = 'asc';

    /**
     * @var string
     *
     * @ORM\Column(name="api_token", type="string")
     */
    private $apiToken;

    /**
     * @var string
     *
     * @ORM\Column(name="feed_token", type="string")
     */
    private $feedToken;

    /**
     * @var string
     *
     * @ORM\Column(name="auth_google_token", type="string")
     */
    private $authGoogleToken;

    /**
     * @var string
     *
     * @ORM\Column(name="auth_mozilla_token", type="string")
     */
    private $authMozillaToken;

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
    public function getPreferredLocale()
    {
        return $this->preferredLocale;
    }

    /**
     * @param string $preferredLocale
     */
    public function setPreferredLocale($preferredLocale)
    {
        $this->preferredLocale = $preferredLocale;
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
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return string
     */
    public function getItemsSortingDirection()
    {
        return $this->itemsSortingDirection;
    }

    /**
     * @param string $itemsSortingDirection
     */
    public function setItemsSortingDirection($itemsSortingDirection)
    {
        $this->itemsSortingDirection = $itemsSortingDirection;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }
}
