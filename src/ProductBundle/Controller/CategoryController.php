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
     * @Template("ProductBundle:Search:index.html.twig")
     */
    public function indexAction(Request $request, $path)
    {

        $pathElems = explode('/', $path);

        $repository = $this
            ->getDoctrine()
            ->getRepository('ProductBundle:Category');

        $mainCategories = $repository
            ->findByParentCache(null);

        $categories = $repository
            ->findBySlugCache(end($pathElems));


        foreach ($categories as $potentialCategory) {

            if ($potentialCategory->getPath() == $path) {
                $category = $potentialCategory;
            }
        }

        if (!isset($category)) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $search = $this->get('product_bundle.product_search_service');
        $results = $search->search([
            'cat' => $category->getId()
        ]);
        $params = $request->query->all();
        $pagination = $search->getHtmlPagination($results, array_merge($params, ['cat' => $category->getId()]));

        return [
            'category' => $category,
            'products' => $results,
            'pagination' => $pagination,
            'mainCategories' => $mainCategories
        ];
    }
}