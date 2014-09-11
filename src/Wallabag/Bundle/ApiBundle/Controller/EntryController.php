<?php


namespace Wallabag\Bundle\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation\Inject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Wallabag\Bundle\CoreBundle\Document\Entry;
use Wallabag\Bundle\CoreBundle\Document\User;
use Wallabag\Bundle\CoreBundle\Service\EntryService;

class EntryController {
    /**
     * @var EntryService
     * @Inject("wallabag_core.entry")
     */
    private $entryService;

    /**
     * @Get("/u/{user}/entry/{entry}", methods={"GET"})
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     * @ParamConverter("entry", options={"id"="entry"})
     * @View(statusCode=200, serializerGroups={"entries"})
     */
    public function getAction(User $user, Entry $entry) {
        return $entry;
    }
} 