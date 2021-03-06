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
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $links = $this->linkRepository->findForOverview(LinkRepository::SORT_RECENT);

        return array(
            'links' => $links,
        );
    }
}
