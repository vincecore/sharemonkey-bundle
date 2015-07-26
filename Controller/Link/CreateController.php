<?php

namespace ShareMonkey\ShareMonkeyBundle\Controller\Link;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateController
{
    /**
     * @param Request $request
     * @return Response
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array(
        );
    }
}
