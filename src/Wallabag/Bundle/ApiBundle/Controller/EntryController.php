<?php


namespace Wallabag\Bundle\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation\Inject;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\Bundle\CoreBundle\Document\Entry;
use Wallabag\Bundle\CoreBundle\Document\User;
use Wallabag\Bundle\CoreBundle\Service\EntryService;

class EntryController {
    /**
     * @var EntryService
     * @Inject("wallabag_core.services.entry")
     */
    private $entryService;

    /**
     * @var LoggerInterface
     * @Inject("logger")
     */
    private $logger;

    /**
     * @var Router
     * @Inject("router")
     */
    private $router;

    /**
     * Fetch an entry, regardless the status flags
     *
     * @Get("/u/{user}/entry/{entry}")
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     * @ParamConverter("entry", options={"id"="entry"})
     * @View(statusCode=200, serializerGroups={"entries"})
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The username"},
     *          {"name"="entry", "dataType"="string", "requirement"="\w+", "description"="The entry ID"}
     *      }
     * )
     */
    public function getAction(User $user, Entry $entry) {
        $this->logger->info("User {username} wants to show entry {url}",
            array("username" => $user->getUsername(), "url" => $entry->getUrl()));
        return $entry;
    }

    /**
     * Change several properties of an entry. I.E tags, archived, starred and deleted status
     *
     * @Patch("/u/{user}/entry/{entry}")
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     * @ParamConverter("entry", options={"id"="entry"})
     * @View(statusCode=204)
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The username"}
     *      }
     * )
     */
    public function patchAction(User $user, Entry $entry, Request $request) {
        $request->request->get("tags", array());
        $request->request->get("archived");
        $request->request->get("deleted");
        $request->request->get("starred");

        $view = \FOS\RestBundle\View\View::create();
        $view->setStatusCode(Response::HTTP_NO_CONTENT);
        $view->setLocation($this->router->generate("wallabag_api_entry_get", array(
                    "user" => $user->getUsername(),
                    "entry" => $entry->getId()
                )));
        return $view;
    }
} 