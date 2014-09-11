<?php

namespace Wallabag\Bundle\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation\Inject;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\Bundle\CoreBundle\Document\User;
use Wallabag\Bundle\CoreBundle\Service\EntryService;

class EntriesController
{
    /**
     * @var EntryService
     * @Inject("wallabag_core.services.entry")
     */
    private $entryService;

    /**
     * @var Router
     * @Inject("router")
     */
    private $router;

    /**
     * Save a new entry for the given user
     *
     * @Post("/u/{user}/entries")
     * @ParamConverter("user",options={"mapping": {"user": "username"}})
     * @View(statusCode=201)
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The username"}
     *      },
     *      parameters={
     *          {"name"="url", "dataType"="string", "required"=true, "description"="The URL to save"},
     *          {"name"="tags", "dataType"="string[]", "required"=false, "description"="The tags for this entry"}
     *      })
     */
    public function postAction(Request $request, User $user)
    {
        $url = $request->request->get("url");
        $tags = $request->request->get("tags", array());

        $entry = $this->entryService->save($user, $url, $tags);

        $view = \FOS\RestBundle\View\View::create();
        $view->setData($entry);
        $view->setStatusCode(Response::HTTP_CREATED);
        $view->setLocation($this->router->generate("wallabag_api_entry_get", array(
                    "user" => $user->getUsername(),
                    "entry" => $entry->getId()
                )));
        return $view;
    }

    /**
     * List unread entries for the given user
     *
     * @Get("/u/{user}/entries", methods={"GET"})
     * @Get("/u/{user}", methods={"GET"})
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     * @View(statusCode=200, serializerGroups={"entries"})
     * @ApiDoc(
     *      requirements={
     *          {"name"="user", "dataType"="string", "requirement"="\w+", "description"="The username"}
     *      }
     * )
     *
     * @param User $user the username
     */
    public function getAction(User $user) {
        return array_values($this->entryService->listForUser($user));
    }
}
