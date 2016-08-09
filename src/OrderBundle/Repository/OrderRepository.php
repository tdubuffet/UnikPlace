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
            ->orderBy('o.updatedAt', 'DESC')
            ->getQuery()
        ;

    }

    public function findSaleByUser(User $user)
    {

        return $this->createQueryBuilder('o')
            ->innerJoin('o.product', 'p', 'WITH', 'p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.updatedAt', 'DESC')
            ->getQuery()
        ;
    }

    public function getTotalAmount($preAuthId)
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->join('o.status', 's', 'WITH', 's.name = :status')
            ->where('o.mangopayPreauthorizationId = :preAuthId')
            ->setParameter('preAuthId', $preAuthId)
            ->setParameter('status', 'pending')
            ->groupBy('o.mangopayPreauthorizationId')
            ->getQuery()
            ->getSingleResult()
        ;
    }

}
