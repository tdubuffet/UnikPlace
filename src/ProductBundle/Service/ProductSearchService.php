<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;
use Elastica\Query\BoolQuery;
use Pagerfanta\View\TwitterBootstrap3View;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Category;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ProductSearchService
{

    /** @var $finder TransformedFinder */
    private $finder;

    /** @var $router Router */
    private $router;

    /** @var EntityManager */
    private $em;

    /** @var \Twig_Environment */
    private $twig;

    /** @var AttributValue */
    private $attributValue;

    public function __construct($finder, $router, EntityManager $entityManager, $twig, AttributValue $attributValue)
    {
        $this->finder = $finder;
        $this->router = $router;
        $this->em = $entityManager;
        $this->twig = $twig;
        $this->attributValue = $attributValue;
    }


    /**
     * Perform a product search
     *
     * @param array $params Search parameters
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function search($params)
    {
        $maxPerPage = isset($params['limit']) && in_array($params['limit'], [12, 24, 36]) ? $params['limit'] : 12; // Products per page
        $currentPage = isset($params['p']) ? $params['p'] : 1;

        // Build search query
        $boolQuery = new \Elastica\Query\BoolQuery();
        $this->applyQuery($boolQuery, $params);
        $this->applyCategory($boolQuery, $params);
        $this->applyStatus($boolQuery);
        $this->applyPrice($boolQuery, $params);
        $this->applyAttributes($boolQuery, $params);
        $this->applyCounty($boolQuery, $params);
        $this->applyUser($boolQuery, $params);

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
     * @param array\\PagerFanta\PagerFanta $results Search results
     * @param array $params Search parameters
     *
     * @return string The HTML code
     */
    public function getHtmlPagination($results, $params)
    {
        $routeGenerator = function ($page) use ($params) {
            return $this->router->generate('search', array_merge($params, ['p' => $page]));
        };
        $view = new TwitterBootstrap3View();
        $options = array(
            'proximity' => 9,
            'prev_message' => '← Précèdent',
            'next_message' => 'Suivant →'
        );

        if ($results->getNbPages() == 1) {
            return '';
        }


        $pagination = $view->render($results, $routeGenerator, $options);

        return $pagination;
    }

    /**
     * Generate HTML code for search attribute filters
     *
     * @param \ProductBundle\Entity\Category $category The product category selected
     *
     * @return string The HTML code
     */
    public function getHtmlFilters($category = null)
    {
        /** @var Category $category */
        $filters['price'] = ['template' => 'price']; // Price filter is always displayed
        if ($category) {
            $attributes = $category->getAttributes();

            if (!is_array($attributes)) {
                $attributes = $attributes->toArray();
            }

            if ($category->getParent() == null) {
                foreach($category->getChildren() as $children) {

                    $newAttributes = $children->getAttributes();
                    if (!is_array($newAttributes)) {
                        $newAttributes = $newAttributes->toArray();
                    }
                    $attributes = array_merge($attributes, $newAttributes);
                }
            }

            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                $template = $attribute->getAttributeSearchTemplate();
                $filters[$attribute->getCode()] = [
                    'template' => $template->getName(),
                    'viewVars' => [
                        'label' => $attribute->getName(),
                        'id' => $attribute->getCode(),
                    ],
                ];
                $referential = $attribute->getReferential();
                if (isset($referential)) {
                    $filters[$attribute->getCode()]['viewVars']['referentialValues'] = true;
                }
            }
        }
        $html = '';
        $filters['county'] = ['template' => 'county'];
        foreach ($filters as $filter) {

            if (isset($filter['viewVars']['referentialValues'])) {

                //Load Referential values used
                $filter['viewVars']['referentialValues'] = $this->attributValue->orderAttributes(
                    $filter['viewVars']['id'],
                    $category
                );

                //No values used => disabled
                if (count($filter['viewVars']['referentialValues']) == 0) {
                    continue;
                }

            }

            $html .= $this->twig->render(
                'ProductBundle:SearchFilters:'.$filter['template'].'.html.twig',
                isset($filter['viewVars']) ? $filter['viewVars'] : []
            );
        }

        return $html;
    }

    /**
     * @param BoolQuery $boolQuery
     * @param $params
     */
    private function applyQuery($boolQuery, $params)
    {
        if (isset($params['q']) && trim($params['q']) != '') {
            $q = trim($params['q']);
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setFields(['name', 'description']);
            $queryString->setDefaultOperator('AND');
            $queryString->setQuery($q);
            $boolQuery->addMust($queryString);
        }
    }

    /**
     * @param BoolQuery $boolQuery
     * @param $params
     */
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

    /**
     * @param BoolQuery $boolQuery
     * @param $params
     */
    private function applyCounty($boolQuery, $params)
    {
        if (isset($params['county'])) {
            $county = $this->em->getRepository("LocationBundle:County")->findOneBy(['id' => $params['county']]);
            if (isset($county)) {
                $fieldTerm = new \Elastica\Query\Match();
                $fieldTerm->setFieldQuery('county', $county->getId());
                $boolQuery->addMust($fieldTerm);
            }

        }

    }

    /**
     * @param BoolQuery $boolQuery
     * @param $params
     */
    private function applyUser($boolQuery, $params)
    {
        if (isset($params['user'])) {
            $user = $this->em->getRepository("UserBundle:User")->findOneBy(['id' => $params['user']]);
            if (isset($user)) {
                $fieldTerm = new \Elastica\Query\Match();
                $fieldTerm->setFieldQuery('user', $user->getId());
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
                $range['from'] = $priceRange[0];
            }
            if (isset($priceRange[1])) {
                $range['to'] = $priceRange[1];
            }
            if (!empty($range)) {
                $rangeFilter->addField('price', $range);
                $boolQuery->addMust($rangeFilter);
            }
        }
    }

    /**
     * @param BoolQuery $boolQuery
     * @param $params
     */
    private function applyAttributes($boolQuery, $params)
    {
        // We need to fetch all product attributes
        $attributes = $this->em->getRepository('ProductBundle:Attribute')->findAll();
        $attributeParams = [];
        $attributeParamsRange = [];
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if (isset($params[$attribute->getCode()])) {
                $value = $params[$attribute->getCode()];
                if ($attribute->getAttributeSearchTemplate()->getName() == "range") {
                    $range = explode("-", $value);
                    if (isset($range[0])) {
                        $attributeParamsRange[$attribute->getCode()]['from'] = $range[0];
                    }
                    if (isset($range[1])) {
                        $attributeParamsRange[$attribute->getCode()]['to'] = $range[1];
                    }
                }else {
                    $values = array_filter(explode(',', $value)); // Support multi selection on the same attribute
                    if (!empty($values)) {
                        $attributeParams[$attribute->getCode()] = $values;
                    }
                }
            }
        }
        foreach ($attributeParams as $key => $values) {
            $queryString = new \Elastica\Query\QueryString();
            $queryString->setDefaultField($key);
            $queryString->setQuery(implode(' OR ', $values));
            $boolQuery->addMust($queryString);
        }

        foreach ($attributeParamsRange as $key => $objects) {
            $rangeFilter = new \Elastica\Query\Range();
            $rangeFilter->addField($key, $objects);
            $boolQuery->addMust($rangeFilter);
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

    private function applyStatus($boolQuery)
    {
        $fieldTerm = new \Elastica\Query\Terms();
        $fieldTerm->setTerms('status', ['sold', 'published']);
        $boolQuery->addMust($fieldTerm);
    }

}