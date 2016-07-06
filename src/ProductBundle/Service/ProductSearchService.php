<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;
use Pagerfanta\View\TwitterBootstrap3View;

class ProductSearchService
{

    /**
     *
     * @var $finder FOS\ElasticaBundle\Finder\TransformedFinder
     */
    private $finder;

    /**
     *
     * @var $route Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $route;

    /**
     *
     * @var EntityManager
     */
    private $em;

    /**
     *
     * @var Twig_Environment
     */
    private $twig;

    public function __construct($finder, $router, EntityManager $entityManager, $twig)
    {
        $this->finder = $finder;
        $this->router = $router;
        $this->em = $entityManager;
        $this->twig = $twig;
    }


    /**
     * Perform a product search
     *
     * @param array $params Search parameters
     *
     * @return Pagerfanta\Pagerfanta
     */
    public function search($params)
    {
        $maxPerPage = 3; // Products per page
        $currentPage = isset($params['p']) ? $params['p'] : 1;

        // Build search query
        $boolQuery = new \Elastica\Query\Bool();
        $this->applyQuery($boolQuery, $params);
        $this->applyCategory($boolQuery, $params);
        $this->applyPrice($boolQuery, $params);

        $query = new \Elastica\Query($boolQuery);
        $this->applySortAndOrder($query, $params);

        $results = $this->finder->findPaginated($query);
        $results->setMaxPerPage($maxPerPage);
        $results->setCurrentPage($currentPage);
        return $results;
    }

    /**
     * Generate HTML code for search pagination
     *
     * @param array $results Search results
     * @param array $params Search parameters
     *
     * @return string The HTML code
     */
    public function getHtmlPagination($results, $params)
    {
        $routeGenerator = function($page) use ($params) {
            return $this->router->generate('search', array_merge($params, ['p' => $page]));
        };
        $view = new TwitterBootstrap3View();
        $options = array('proximity' => 3,
                         'prev_message' => '← Précèdent',
                         'next_message' =>'Suivant →');
        $pagination = $view->render($results, $routeGenerator, $options);
        return $pagination;
    }

    /**
     * Generate HTML code for search attribute filters
     *
     * @param ProductBundle\Entity\Category $category The product category selected
     *
     * @return string The HTML code
     */
    public function getHtmlFilters($category = null)
    {
        $filters = ['price' => ['template' => 'price']];
        if ($category) {
            $attributes = $category->getAttributes();
        }
        $html = '';
        foreach ($attributes as $attribute) {
            $template = $attribute->getAttributeTemplate();
            $filters[$attribute->getCode()] = ['template' => $template->getName(),
                                               'viewVars' => ['label' => $attribute->getName()]];
            $referential = $attribute->getReferential();
            if (isset($referential)) {
                $filters[$attribute->getCode()]['viewVars']['referentialValues'] = $referential->getReferentialValues();
            }
        }
        foreach ($filters as $filter) {
            $html .= $this->twig->render('ProductBundle:SearchFilters:'.$filter['template'].'.html.twig',
                                         isset($filter['viewVars']) ? $filter['viewVars'] : []);
        }
        return $html;
    }

    private function applyQuery($boolQuery, $params)
    {
        if (isset($params['q']) && $params['q'] != '') {
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('name', $params['q']);
            $boolQuery->addShould($fieldQuery);

            $fieldQuery = new \Elastica\Query\Fuzzy();
            $fieldQuery->setField('name', $params['q']);
            $boolQuery->addShould($fieldQuery);

            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('description', $params['q']);
            $boolQuery->addShould($fieldQuery);
        }
    }

    private function applyCategory($boolQuery, $params)
    {
        if (isset($params['cat']) && is_numeric($params['cat'])) {
            $category = $this->em->getRepository('ProductBundle:Category')->findOneById($params['cat']);
            if (isset($category)) {
                $fieldTerm = new \Elastica\Query\Term();
                $fieldTerm->setTerm('category', $category->getPath());
                $boolQuery->addMust($fieldTerm);
            }
        }
    }

    private function applyPrice($boolQuery, $params)
    {
        if (isset($params['price'])) {
            $priceRange = array_filter(explode('-', $params['price']));
            $rangeFilter = new \Elastica\Query\Range();
            $range = array();
            if (isset($priceRange[0])) {
                $range['from']= $priceRange[0];
            }
            if (isset($priceRange[1])) {
                $range['to']= $priceRange[1];
            }
            if (!empty($range)) {
                $rangeFilter->addField('price', $range);
                $boolQuery->addMust($rangeFilter);
            }
        }
    }

    private function applySortAndOrder($bool, $params)
    {
        if (isset($params['sort']) && $params['sort'] == 'relevance') {
            return; // No sort for relevance
        }
        $fields = array(
            'new' => 'updated_at',
            'name' => 'name',
            'price' => 'price',
        );
        $field = $fields['new'];
        if (isset($params['sort']) && isset($fields[$params['sort']])) {
            $field = $fields[$params['sort']];
        }
        $order = isset($params['ord']) && in_array($params['ord'], ['asc', 'desc']) ? $params['ord'] : 'desc';
        $bool->addSort(array($field => array('order' => $order)));
    }

}