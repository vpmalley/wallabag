<?php


namespace Wallabag\Bundle\CoreBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Psr\Log\LoggerInterface;
use Wallabag\Bundle\CoreBundle\Document\Bookmark;
use Wallabag\Bundle\CoreBundle\Document\Entry;
use Wallabag\Bundle\CoreBundle\Document\Tag;
use Wallabag\Bundle\CoreBundle\Document\User;
use Wallabag\Bundle\CoreBundle\Repository\EntryRepository;
use Wallabag\Bundle\CoreBundle\Url\Url;
use Wallabag\Bundle\CoreBundle\Url\UrlFetcher;

/**
 * Class BookmarkService
 * @package Wallabag\Bundle\CoreBundle\Service
 * @Service(id="wallabag_core.services.entry")
 */
class EntryService {
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlFetcher
     */
    private $urlFetcher;

    /**
     * @var \HTMLPurifier
     */
    private $htmlPurifier;

    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @InjectParams({
     *      "dm" = @Inject("doctrine_mongodb.odm.document_manager"),
     *      "logger" = @Inject("logger"),
     *      "urlFetcher" = @Inject("full_text_rss_url_fetcher"),
     *      "htmlPurifier" = @Inject("wallabag_core.html_purifier"),
     *      "entryRepository" = @Inject("wallabag_core.repository.entry")
     * })
     * @param DocumentManager $dm
     * @param UrlFetcher $urlFetcher
     */
    public function __construct(DocumentManager $dm, LoggerInterface $logger, UrlFetcher $urlFetcher,
            \HTMLPurifier $htmlPurifier, EntryRepository $entryRepository) {
        $this->dm = $dm;
        $this->logger = $logger;
        $this->urlFetcher = $urlFetcher;
        $this->htmlPurifier = $htmlPurifier;
        $this->entryRepository = $entryRepository;
    }

    /**
     * Fetch and save an entry and assign it to a user
     *
     * @param User $user
     * @param string $url
     * @param string[] $tags
     * @return Entry the created entry
     */
    public function save(User $user, $url, $tags = array()) {
        $content = $this->urlFetcher->fetch($url);
        $this->purifyUrl($content);

        $entry = $this->entryRepository->findOneByUserAndUrl($user->getId(), $content->getUrl());
        if($entry === null) {
            $entry = new Entry();
            $entry->setUser($user);
            $entry->setUrl($content->getUrl());
            $entry->setCreatedAt(new \DateTime());
        }
        $entry->setContent($content->getContent());
        $entry->setTitle($content->getTitle());
        $entry->setArchived(false);

        foreach($tags as $tag) {
            $entry->addTag(new Tag($tag));
        }

        $this->dm->persist($entry);
        $this->dm->flush();

        return $entry;
    }

    /**
     * List all non-archived entries for a given user
     *
     * @param User $user the user
     * @return \Wallabag\Bundle\CoreBundle\Document\Entry[]
     */
    public function listForUser(User $user) {
        return $this->entryRepository->findUnreadByUser($user->getId());
    }

    /**
     * Purify HTML contents of an URL : title and content fields
     *
     * @param Url $url the URL Object to purify
     */
    private function purifyUrl(Url $url) {
        $url->setTitle($this->htmlPurifier->purify($url->getTitle()));
        $url->setContent($this->htmlPurifier->purify($url->getContent()));
    }

    /**
     * Change updatable fields of an entry an persists it
     *
     * @param Entry $entry the entry
     * @param string[] $tags the new tags
     * @param boolean $archived the new archived status
     * @param boolean $starred the new starred status
     * @param boolean $deleted the new deleted status
     */
    public function updateEntry(Entry $entry, $tags, $archived, $starred, $deleted) {
        $managed = $this->dm->merge($entry);

        if(is_array($tags)) {
            $managed->flushTags();
            foreach ($tags as $tag) {
                $managed->addTag(new Tag($tag));
            }
        }
        if($archived != null) {
            $entry->setArchived($archived);
        }
        if($starred != null) {
            $entry->setStarred($starred);
        }
        if($deleted != null) {
            $entry->setDeleted($deleted);
        }

        $this->dm->flush();
    }
} 