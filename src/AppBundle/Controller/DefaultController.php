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
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findAllForNoCategories();

        return ["collections" => $collections];
    }

    /**
     * @Route("/a-propos", name="about")
     * @Template("AppBundle:default:about.html.twig")
     */
    public function aboutAction(Request $request)
    {
        $viewVars['magicNumber'] = rand(1, 100);

        return $viewVars;
    }

    /**
     * @Template("AppBundle:default:categories.html.twig")
     */
    public function categoriesAction()
    {

        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findByParentCache(null);
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findLast10();

        return ['categories' => $categories, "collections" => $collections];
    }

    /**
     * @Template("AppBundle:default:searchcategories.html.twig")
     */
    public function searchCategoriesAction()
    {

        $categories = $this->getDoctrine()
            ->getRepository('ProductBundle:Category')
            ->findByParentCache(null);

        return array('categories' => $categories);
    }

    /**
     * @Template("AppBundle:default:mobilecategories.html.twig")
     */
    public function mobileCategoriesAction()
    {

        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findByParentCache(null);
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findLast10();

        return ['categories' => $categories, "collections" => $collections];
    }
}
