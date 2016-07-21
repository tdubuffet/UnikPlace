<?php

namespace ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends EntityRepository
{

    public function findByParentCache($parent)
    {
        $q = $this->createQueryBuilder('c');

        if ($parent == null) {
            $q->where('c.parent IS NULL');
        } else {
            $q->where('c.parent = :parent')->setParameter('parent', $parent);
        }

        return $q->getQuery()
            ->useResultCache(true, 3600, 'list_categories_by_parent')
            ->getResult();
    }

    public function findBySlugCache($slug)
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->useResultCache(true, 3600, 'list_categories-_by_slug')
            ->getResult();
    }

}
