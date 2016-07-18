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
        $categories = $this
            ->get('apc_cache')
            ->fetch('menu_categories');

        if (!$categories) {

            $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
            $categories = $repository->findBy(array('parent' => null));

            $this->get('apc_cache')->save(
                'menu_categories',
                $categories,
                60
            );
        }

        return array('categories' => $categories);
    }

    /**
     * @Template("AppBundle:default:searchcategories.html.twig")
     */
    public function searchCategoriesAction()
    {
        $categories = $this
            ->get('apc_cache')
            ->fetch('search_categories');

        if (!$categories) {

            $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
            $categories = $repository->findBy(array('parent' => null));

            $this->get('apc_cache')->save(
                'search_categories',
                $categories,
                60
            );
        }

        return array('categories' => $categories);
    }

    /**
     * @Template("AppBundle:default:mobilecategories.html.twig")
     */
    public function mobileCategoriesAction()
    {
        $categories = $this
            ->get('apc_cache')
            ->fetch('search_mobile_categories');

        if (!$categories) {

            $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
            $categories = $repository->findBy(array('parent' => null));

            $this->get('apc_cache')->save(
                'search_mobile_categories',
                $categories,
                60
            );
        }

        return array('categories' => $categories);
    }
}
