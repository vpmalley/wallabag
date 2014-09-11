<?php

namespace Wallabag\Bundle\CoreBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Entry
 *
 * @MongoDB\Document(repositoryClass="Wallabag\Bundle\CoreBundle\Repository\EntryRepository",
 *      indexes={
 *          @MongoDB\Index(keys={"user" = "asc", "url" = "asc"}, options={"unique"="true"})
 *      })
 */
class Entry
{
    /**
     * @var integer
     *
     * @MongoDB\Id(strategy="auto")
     */
    private $id;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     * @Groups({"entries"})
     */
    private $url;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     * @Groups({"entries"})
     */
    private $title;

    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     * @Groups({"entries"})
     */
    private $content;

    /**
     * @var User
     *
     * @MongoDB\ReferenceOne(targetDocument="Wallabag\Bundle\CoreBundle\Document\User")
     */
    private $user;

    /**
     * @var \DateTime
     * @MongoDB\Date
     * @Expose
     * @Groups({"entries"})
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     * @MongoDB\EmbedMany(targetDocument="Wallabag\Bundle\CoreBundle\Document\Tag")
     * @Expose
     * @Groups({"entries"})
     */
    private $tags;

    /**
     * @var boolean
     * @MongoDB\Boolean
     */
    private $archived = false;

    /**
     * @var boolean
     * @MongoDB\Boolean
     */
    private $deleted = false;

    /**
     * @var boolean
     * @MongoDB\Boolean
     */
    private $starred = false;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Add tag
     *
     * @param \Wallabag\Bundle\CoreBundle\Document\Tag $tag
     */
    public function addTag(\Wallabag\Bundle\CoreBundle\Document\Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * Remove tag
     *
     * @param \Wallabag\Bundle\CoreBundle\Document\Tag $tag
     */
    public function removeTag(\Wallabag\Bundle\CoreBundle\Document\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    public function flushTags() {
        $this->tags->clear();
    }

    /**
     * Get tags
     *
     * @return Tag[] $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set archived
     *
     * @param boolean $archived
     * @return self
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
        return $this;
    }

    /**
     * Get archived
     *
     * @return boolean $archived
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return self
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set starred
     *
     * @param boolean $starred
     * @return self
     */
    public function setStarred($starred)
    {
        $this->starred = $starred;
        return $this;
    }

    /**
     * Get starred
     *
     * @return boolean $starred
     */
    public function getStarred()
    {
        return $this->starred;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }
}
