<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;
use ProductBundle\Entity\Category;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class AttributValue
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function orderAttributes($type, $category = null)
    {

        $key = $type;

        if ($category) {
            $key .= '_' . $category->getId();
        }

        $cache = $this->container->get('app_cache')->fetch($key);

        if ($cache) {
            return $cache;
        }

        $array = [];
        $results = $this->query($category, $type);

        if ($results->getAggregation('type')) {
            $buckets = $results->getAggregation('type')['buckets'];

            $ids = [];

            foreach($buckets as $bucket) {
                $ids[] = $bucket['key'];
            }

            $array = $this->container->get('doctrine')->getRepository('ProductBundle:ReferentialValue')->findById($ids, ['value' => 'ASC']);

        }

        $this->container->get('app_cache')->save($key, $array, 60);


        return $array;

    }

    private function query($category, $type) {
        $boolQuery = new \Elastica\Query\BoolQuery();

        $this->applyCategory($boolQuery, $category);
        $this->applyStatus($boolQuery);

        $query = new \Elastica\Query($boolQuery);

        $tagsAggregation = new \Elastica\Aggregation\Terms('type');
        $tagsAggregation->setField(strtolower($type));

        $query->addAggregation($tagsAggregation);

        $query->setSize(0);

        $results = $this->container->get('fos_elastica.index.noname.product')->search($query);

        return $results;
    }

    /**
     * @param BoolQuery $boolQuery
     * @param $params
     */
    private function applyCategory($boolQuery, $category)
    {
        if (isset($category)) {
            $fieldTerm = new \Elastica\Query\Term();
            $fieldTerm->setTerm('category', $category->getPath());
            $boolQuery->addMust($fieldTerm);
        }

    }

    private function applyStatus($boolQuery)
    {
        $fieldTerm = new \Elastica\Query\Terms();
        $fieldTerm->setTerms('status', ['sold', 'published']);
        $boolQuery->addMust($fieldTerm);
    }
}