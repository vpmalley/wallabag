<?php

namespace Wallabag\Bundle\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation\Inject;
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
     * @Inject("wallabag_core.entry")
     */
    private $entryService;

    /**
     * @var Router
     * @Inject("router")
     */
    private $router;

    /**
     * @Post("/u/{user}/entries")
     * @ParamConverter("user",options={"mapping": {"user": "username"}})
     * @View(statusCode=201)
     */
    public function postAction(Request $request, User $user)
    {
        $url = $request->request->get("url");
        $tags = $request->request->get("tags");
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
     * @Get("/u/{user}/entries", methods={"GET"})
     * @Get("/u/{user}", methods={"GET"})
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     * @View(statusCode=200, serializerGroups={"entries"})
     */
    public function getAction(User $user) {
        return array_values($this->entryService->listForUser($user));
    }
}
