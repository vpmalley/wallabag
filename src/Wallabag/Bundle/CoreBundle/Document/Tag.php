<?php

namespace Wallabag\Bundle\CoreBundle\Document;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Tag
 *
 * @MongoDB\EmbeddedDocument
 */
class Tag
{
    /**
     * @var string
     *
     * @MongoDB\String
     * @Expose
     * @Groups({"entries"})
     */
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Tag
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }
}
