<?php

namespace ProductBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    /**
     * @Route("/recherche", name="search", options={"expose"=true})
     * @Template("ProductBundle:Search:index.html.twig")
     */
    public function searchAction(Request $request)
    {
        $params         = $request->query->all();
        $search         = $this->get('product_bundle.product_search_service');
        $results        = $search->search($params);
        $pagination     = $search->getHtmlPagination($results, $params);

        $repository = $this->getDoctrine()
            ->getRepository('ProductBundle:Category');
        $mainCategories = $repository
            ->findBy(array('parent' => null));

        return [
            'products' => $results,
            'mainCategories' => $mainCategories,
            'pagination' => $pagination
        ];
    }


    /**
     * @Route("/ajax/recherche", name="ajax_search", options={"expose"=true})
     * @Template("ProductBundle:Search:product_grid.html.twig")
     * @Method({"POST"})
     */
    public function postSearchAction(Request $request)
    {
        $params = $request->request->all();
        $search = $this->get('product_bundle.product_search_service');
        $results = $search->search($params);
        $pagination = $search->getHtmlPagination($results, $params);
        return ['products' => $results, 'pagination' => $pagination];
    }

    /**
     * @Route("/ajax/recherche/filtres", name="ajax_search_attribute_filters", options={"expose"=true})
     * @Method({"POST"})
     */
    public function searchFilters(Request $request)
    {
        $categoryId = $request->request->get('category_id');
        $category = null;
        if ($categoryId) {
            $category = $this->getDoctrine()
                ->getRepository('ProductBundle:Category')
                ->findOneById($categoryId);
        }

        $search = $this->get('product_bundle.product_search_service');

        $html = $search->getHtmlFilters($category);

        return new Response($html);
    }
}