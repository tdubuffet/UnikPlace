<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 19/07/16
 * Time: 15:57
 */

namespace MessageBundle\Repository;


use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    public function findExistsThreadByProductAndUser($product, $user)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')


            ->andWhere('t.product = :product')
            ->setParameter('product', $product)

            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $user->getId())

            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false, \PDO::PARAM_BOOL)

            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false, \PDO::PARAM_BOOL)

            ->getQuery()
            ->getResult();
    }

    public function findThreadByProductAndUser($product, $user)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')


            ->andWhere('t.product = :product')
            ->setParameter('product', $product)

            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $user->getId())

            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false, \PDO::PARAM_BOOL)

            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false, \PDO::PARAM_BOOL)

            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findThreadByUser($user)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')

            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $user->getId())

            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false, \PDO::PARAM_BOOL)

            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false, \PDO::PARAM_BOOL)

            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findThreadByProductAndUsers($product, $usersIds)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('tm.participant', 'p')


            ->andWhere('t.product = :product')
            ->setParameter('product', $product)

            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $usersIds)

            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false, \PDO::PARAM_BOOL)

            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false, \PDO::PARAM_BOOL)

            ->getQuery()
            ->getOneOrNullResult();
    }
}