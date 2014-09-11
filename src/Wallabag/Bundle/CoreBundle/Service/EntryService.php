<?php


namespace Wallabag\Bundle\CoreBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Wallabag\Bundle\CoreBundle\Document\Bookmark;
use Wallabag\Bundle\CoreBundle\Document\Entry;
use Wallabag\Bundle\CoreBundle\Document\Tag;
use Wallabag\Bundle\CoreBundle\Document\User;

/**
 * Class BookmarkService
 * @package Wallabag\Bundle\CoreBundle\Service
 * @Service(id="wallabag_core.entry")
 */
class EntryService {
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @InjectParams({
     *      "dm" = @Inject("doctrine_mongodb.odm.document_manager")
     * })
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * @param User $user
     * @param string $url
     * @param string[] $tags
     * @return Entry the created entry
     */
    public function save(User $user, $url, $tags = array()) {
        $entry = new Entry();
        $entry->setUser($user);
        $entry->setUrl($url);
        $entry->setCreatedAt(new \DateTime());
        $entry->setContent("Fixture content");
        $entry->setTitle("Fixture title");
        $entry->setArchived(false);
        $entry->setDeleted(false);
        $entry->setStarred(false);

        foreach($tags as $tag) {
            $entry->addTag(new Tag($tag));
        }

        $this->dm->persist($entry);
        $this->dm->flush();

        return $entry;
    }

    public function listForUser(User $user) {
        $bookmarkRepository = $this->dm->getRepository('\Wallabag\Bundle\CoreBundle\Document\Entry');
        return $bookmarkRepository->findUnreadByUser($user->getId());
    }
} 