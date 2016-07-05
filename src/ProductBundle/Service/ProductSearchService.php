<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;
use Pagerfanta\View\TwitterBootstrapView;

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

    public function __construct($finder, $router)
    {
        $this->finder = $finder;
        $this->router = $router;
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

        $boolQuery = new \Elastica\Query\Bool();
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

        $results = $this->finder->findPaginated($boolQuery);
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
        $view = new TwitterBootstrapView();
        $options = array('proximity' => 3);
        $pagination = $view->render($results, $routeGenerator, $options);
        return $pagination;
    }

}