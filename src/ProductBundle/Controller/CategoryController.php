<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use ProductBundle\Entity\Category;

class CategoryController extends Controller
{

    /**
     * @Route("/c/{path}", requirements={
     *     "path": "(.+)"
     * }, name="category")
     * @Template("ProductBundle:Category:index.html.twig")
     */
    public function indexAction(Request $request, $path)
    {
        // TODO check complete path
        $pathElems = explode('/', $path);
        $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
        $category = $repository->findOneBy(array('slug' => end($pathElems)));
        return ['category' => $category];
    }
}