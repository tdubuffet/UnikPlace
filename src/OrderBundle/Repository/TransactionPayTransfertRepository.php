<?php

namespace OrderBundle\Repository;
use UserBundle\Entity\User;

/**
 * TransactionPayInRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TransactionPayTransfertRepository extends \Doctrine\ORM\EntityRepository
{

    public function findAllTransactionsByUser(User $user)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.order', 'o')
            ->innerJoin('o.product', 'p', 'WITH', 'p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;

    }
}