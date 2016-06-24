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
        $pathElems = explode('/', $path);
        $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
        $categories = $repository->findBy(array('slug' => end($pathElems)));
        foreach ($categories as $potentialCategory) {
            if ($potentialCategory->getPath() == $path) {
                $category = $potentialCategory;
            }
        }
        if (!isset($category)) {
            throw $this->createNotFoundException('The category does not exist');
        }
        $finder = $this->container->get("fos_elastica.finder.noname.product");
        $boolQuery = new \Elastica\Query\Bool();
        $fieldTerm = new \Elastica\Query\Term();
        $fieldTerm->setTerm('category', $path);
        $boolQuery->addMust($fieldTerm);
        $results = $finder->find($boolQuery);
        return ['category' => $category, 'products' => $results];
    }
}