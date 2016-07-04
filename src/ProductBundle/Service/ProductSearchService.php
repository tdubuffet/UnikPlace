<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;

class ProductSearchService
{

    /**
     *
     * @var FOS\ElasticaBundle\Finder\TransformedFinder
     */
    private $finder;

    public function __construct($finder)
    {
        $this->finder = $finder;
    }


    /**
     * Perform a product search
     *
     * @param array $params
     *
     * @return Pagerfanta\Pagerfanta
     */
    public function search($params)
    {
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
        $results->setMaxPerPage(1);
        $results->setCurrentPage(1);
        return $results;
    }

}