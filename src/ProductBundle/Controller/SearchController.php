<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrapView;

class SearchController extends Controller
{
    /**
     * @Route("/recherche", name="search")
     * @Template("ProductBundle:Search:index.html.twig")
     */
    public function searchAction(Request $request)
    {
        $search = $this->container->get('product_bundle.product_search_service');
        $results = $search->search($request->query->all());

        $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
        $mainCategories = $repository->findBy(array('parent' => null));

        $routeGenerator = function($page) {
            return '#';
        };

        $view = new TwitterBootstrapView();
        $options = array('proximity' => 3);
        $pagination = $view->render($results, $routeGenerator, $options);

        return ['products' => $results, 'mainCategories' => $mainCategories, 'pagination' => $pagination];
    }


    /**
     * @Route("/ajax/recherche", name="ajax_search")
     * @Template("ProductBundle:Search:product_grid.html.twig")
     * @Method({"POST"})
     */
    public function postSearchAction(Request $request)
    {
        $search = $this->container->get('product_bundle.product_search_service');
        $results = $search->search($request->request->all());
        return ['products' => $results];
    }
}