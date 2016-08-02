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

        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findByParentCache(null);

        $productsByCategory = [];

        foreach($categories as $category) {


            $boolQuery = new \Elastica\Query\BoolQuery();
            $fieldTerm = new \Elastica\Query\Term();
            $fieldTerm->setTerm('category', $category->getPath());
            $boolQuery->addMust($fieldTerm);

            $fieldTerm = new \Elastica\Query\Terms();
            $fieldTerm->setTerms('status', ['sold', 'published']);
            $boolQuery->addMust($fieldTerm);

            $query = new \Elastica\Query($boolQuery);
            $results = $this->get('fos_elastica.finder.noname.product')->findPaginated($query);
            $results->setMaxPerPage(3);
            $results->setCurrentPage(1);

            $productsByCategory[$category->getSlug()] = $results;
        }

        return [
            "collections" => $collections,
            "categories" => $categories,
            'productsByCategory' => $productsByCategory
        ];
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
     * @Route("/faq", name="faq")
     * @Template("AppBundle:default:faq.html.twig")
     */
    public function faqAction(Request $request)
    {

        return [];
    }

    /**
     * @Route("/mentions-legales", name="legal_notice")
     * @Template("AppBundle:default:legalNotice.html.twig")
     */
    public function legalNoticeAction(Request $request)
    {

        return [];
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
     * @Template("AppBundle:default:footerShop.html.twig")
     */
    public function footerShopAction()
    {

        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findByParentCache(null);

        return ['categories' => $categories];
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
