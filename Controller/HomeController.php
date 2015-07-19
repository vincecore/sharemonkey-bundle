<?php

namespace ShareMonkey\ShareMonkeyBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ShareMonkey\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    /**
     * @var LinkRepository
     */
    private $linkRepository;

    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    /**
     * @param Request $request
     * @return Response
     * @Template("Home/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $links = $this->linkRepository->findForOverview(
            array(
                'createdTs' => '-1',
            )
        );

        return array(
            'links' => $links,
        );
    }
}
