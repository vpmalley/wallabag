<?php

namespace Wallabag\Bundle\CoreBundle\Controller;

use JMS\DiExtraBundle\Annotation\Inject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\Bundle\CoreBundle\Document\User;
use Wallabag\Bundle\CoreBundle\Service\EntryService;

class BookmarkController
{
    /**
     * @var EntryService
     * @Inject("wallabag_core.bookmark")
     */
    private $bookmarkService;

    /**
     * @Route("/u/{user}/bookmarks", methods={"GET"})
     * @ParamConverter("user",options={"mapping": {"user": "username"}})
     * @Template(template="WallabagCoreBundle:Default:index.html.twig")
     */
    public function saveAction(Request $request, User $user)
    {
        $url = $request->get("url");
        $this->bookmarkService->save($user, $url);
        return array('name' => 'Fabien');
    }

    /**
     * @Route("/u/{user}", methods={"GET"})
     * @ParamConverter("user", options={"mapping": {"user": "username"}})
     * @Template(template="WallabagCoreBundle:Default:index.html.twig")
     */
    public function listAction(Request $request, User $user) {
        $list = $this->bookmarkService->listForUser($user);
        foreach($list as $item) {
            var_dump($item);
        }
        die;
    }
}
