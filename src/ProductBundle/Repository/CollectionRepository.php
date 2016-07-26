<?php

namespace ProductBundle\Repository;

/**
 * CollectionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CollectionRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function findAllForNoCategories()
    {
        return $this->createQueryBuilder("q")
            ->leftJoin("q.categories", "categories")
            ->having("COUNT(categories.id) = 0")
            ->groupBy("q.id")
            ->getQuery()
            ->useResultCache(true, 3600, 'list_collections_no_categories')
            ->getResult();
    }

    /**
     * @return array
     */
    public function findLast10()
    {
        return $this->createQueryBuilder('q')
            ->orderBy("q.id", "DESC")
            ->addOrderBy('q.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->useResultCache(true, 3600, 'list_collections_top_10')
            ->getResult();
    }
}
