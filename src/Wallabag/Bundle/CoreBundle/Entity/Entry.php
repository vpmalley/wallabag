<?php

namespace Wallabag\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entry
 *
 * @ORM\Table(indexes={@ORM\Index(name="idx_status", columns={"status"})})
 * @ORM\Entity(repositoryClass="Wallabag\Bundle\CoreBundle\Repository\EntryRepository")
 */
class Entry
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text")
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="updated", type="boolean")
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="bookmark", type="boolean")
     */
    private $bookmark;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fetched", type="boolean")
     */
    private $fetched;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\Bundle\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Wallabag\Bundle\CoreBundle\Entity\Tag")
     * @ORM\JoinTable(name="tags_entries",
     *      joinColumns={@ORM\JoinColumn(name="entry_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")})
     */
    private $tags;

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
     * Set url
     *
     * @param string $url
     * @return Entry
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Entry
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Entry
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set updated
     *
     * @param boolean $updated
     * @return Entry
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return boolean 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Entry
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set bookmark
     *
     * @param boolean $bookmark
     * @return Entry
     */
    public function setBookmark($bookmark)
    {
        $this->bookmark = $bookmark;

        return $this;
    }

    /**
     * Get bookmark
     *
     * @return boolean 
     */
    public function getBookmark()
    {
        return $this->bookmark;
    }

    /**
     * Set fetched
     *
     * @param boolean $fetched
     * @return Entry
     */
    public function setFetched($fetched)
    {
        $this->fetched = $fetched;

        return $this;
    }

    /**
     * Get fetched
     *
     * @return boolean 
     */
    public function getFetched()
    {
        return $this->fetched;
    }

    /**
     * @return Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Collection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
