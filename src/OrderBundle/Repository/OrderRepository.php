<?php

namespace OrderBundle\Repository;

use UserBundle\Entity\User;

/**
 * OrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderRepository extends \Doctrine\ORM\EntityRepository
{

    public function findPurchaseByUser(User $user)
    {

        return $this->createQueryBuilder('o')
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

    }

    public function findSaleByUser(User $user)
    {

        return $this->createQueryBuilder('o')
            ->innerJoin('o.products', 'p', 'WITH', 'p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

}
