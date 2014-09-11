<?php

namespace Wallabag\Bundle\CoreBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use JMS\DiExtraBundle\Annotation\Service;
use Wallabag\Bundle\CoreBundle\Document\Entry;

/**
 * EntryRepository
 */
class EntryRepository extends DocumentRepository
{
    /**
     * Find all unread bookmarks for a given user
     *
     * @param string $userId the user ID
     * @return Entry[]
     */
    public function findUnreadByUser($userId) {
        return $this->createQueryBuilder('Wallabag\Bundle\CoreBundle\Document\Entry')
            ->field('user.$id')
            ->equals(new \MongoId($userId))
            ->field('archived')
            ->equals(false)
            ->sort('createdAt', 'desc')
            ->eagerCursor()
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @param $userId
     * @param $url
     * @return Entry
     */
    public function findOneByUserAndUrl($userId, $url) {
        return $this->createQueryBuilder('Wallabag\Bundle\CoreBundle\Document\Entry')
            ->field('user.$id')
            ->equals(new \MongoId($userId))
            ->field('url')
            ->equals($url)
            ->getQuery()
            ->getSingleResult();
    }
}