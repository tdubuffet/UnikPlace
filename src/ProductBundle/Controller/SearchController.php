<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    /**
     * @Route("/recherche", name="search")
     * @Template("ProductBundle:Search:index.html.twig")
     */
    public function searchAction(Request $request)
    {
        $params = $request->query->all();
        $search = $this->container->get('product_bundle.product_search_service');
        $results = $search->search($params);
        $pagination = $search->getHtmlPagination($results, $params);

        $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
        $mainCategories = $repository->findBy(array('parent' => null));

        return ['products' => $results, 'mainCategories' => $mainCategories, 'pagination' => $pagination];
    }


    /**
     * @Route("/ajax/recherche", name="ajax_search")
     * @Template("ProductBundle:Search:product_grid.html.twig")
     * @Method({"POST"})
     */
    public function postSearchAction(Request $request)
    {
        $params = $request->request->all();
        $search = $this->container->get('product_bundle.product_search_service');
        $results = $search->search($params);
        $pagination = $search->getHtmlPagination($results, $params);
        return ['products' => $results, 'pagination' => $pagination];
    }
}