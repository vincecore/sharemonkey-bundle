<?php

namespace ShareMonkey\ShareMonkeyBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ShareMonkey\Document\Tag;
use ShareMonkey\Repository\LinkRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController
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
    public function indexAction(Request $request, $tag)
    {
        $links = $this->linkRepository->findByTag(new Tag($tag), LinkRepository::SORT_RECENT);

        return array(
            'links' => $links,
        );
    }
}
