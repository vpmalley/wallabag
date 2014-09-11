<?php


namespace Wallabag\Bundle\CoreBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class Preference
 * @package Wallabag\Bundle\CoreBundle\Document
 * @MongoDB\EmbeddedDocument
 */
class Preference {
    /**
     * @var string
     * @MongoDB\String
     */
    private $locale;

    /**
     * @var int
     * @MongoDB\Int
     */
    private $pageSize;

    /**
     * @var string
     * @MongoDB\String
     */
    private $sortDirection = 'asc';

    /**
     * @var string
     * @MongoDB\String
     */
    private $theme = 'original';

    /**
     * Set locale
     *
     * @param string $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set pageSize
     *
     * @param int $pageSize
     * @return self
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * Get pageSize
     *
     * @return int $pageSize
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set sortDirection
     *
     * @param string $sortDirection
     * @return self
     */
    public function setSortDirection($sortDirection)
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }

    /**
     * Get sortDirection
     *
     * @return string $sortDirection
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    /**
     * Set theme
     *
     * @param string $theme
     * @return self
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get theme
     *
     * @return string $theme
     */
    public function getTheme()
    {
        return $this->theme;
    }
}
