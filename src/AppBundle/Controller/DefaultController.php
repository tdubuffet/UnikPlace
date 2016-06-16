<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("AppBundle:default:index.html.twig")
     */
    public function indexAction(Request $request)
    {
    }

    /**
     * @Route("/about", name="about")
     * @Template("AppBundle:default:about.html.twig")
     */
    public function aboutAction(Request $request) {
        $viewVars['magicNumber'] = rand(1, 100);
        return $viewVars;
    }
}
