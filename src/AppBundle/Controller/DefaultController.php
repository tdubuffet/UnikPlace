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
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findAllForMultiCategories();

        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findByParentCache(null);

        $productsByCategory = [];

        foreach($categories as $category) {


            $boolQuery = new \Elastica\Query\BoolQuery();
            $fieldTerm = new \Elastica\Query\Term();
            $fieldTerm->setTerm('category', $category->getPath());
            $boolQuery->addMust($fieldTerm);

            $fieldTerm = new \Elastica\Query\Terms();
            $fieldTerm->setTerms('status', ['published']);
            $boolQuery->addMust($fieldTerm);

            $query = new \Elastica\Query($boolQuery);
            $query->addSort(['updated_at' => ['order' => 'desc']]);

            $results = $this->get('fos_elastica.finder.noname.product')->findPaginated($query);
            $results->setMaxPerPage(3);
            $results->setCurrentPage(1);

            $productsByCategory[$category->getSlug()] = $results;
        }


        $articles = $this->getDoctrine()->getRepository('BlogBundle:Article')->findBy([
            'published' => true,
        ], ['createdAt' => 'desc'], 3);


        return [
            "collections" => $collections,
            "categories" => $categories,
            'productsByCategory' => $productsByCategory,
            'articles' => $articles
        ];
    }

    /**
     * @Route("/a-propos", name="about")
     * @Template("AppBundle:default:about.html.twig")
     */
    public function aboutAction(Request $request)
    {

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
     * @Route("/qualite-du-contenu", name="quality_content")
     * @Template("AppBundle:default:quality.html.twig")
     */
    public function qualityAction(Request $request)
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
     * @Route("/test", name="test")
     * @Template("AppBundle:default:categories.html.twig")
     */
    public function categoriesAction()
    {

        $categories = $this->getDoctrine()
            ->getRepository('ProductBundle:Category')
            ->findByParentCache(null);

        $styles = [];
        $designers = [];

        foreach ($categories as $cat) {
            $styles[$cat->getId()] = $this->getDoctrine()
                ->getRepository('ProductBundle:AttributeValue')
                ->findStyleByCategory($cat);

            $designers[$cat->getId()] = $this->getDoctrine()
                ->getRepository('ProductBundle:AttributeValue')
                ->findDesignersByCategory($cat);
        }

        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findLast10();

        return [
            'categories' => $categories,
            "collections" => $collections,
            'styles' => $styles,
            'designers' => $designers
        ];
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
