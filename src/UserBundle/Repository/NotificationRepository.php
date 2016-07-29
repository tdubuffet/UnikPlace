<?php

namespace UserBundle\Repository;

use UserBundle\Entity\User;

/**
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotificationRepository extends \Doctrine\ORM\EntityRepository
{

    public function getLastNotificationByUserCache(User $user)
    {

        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->useResultCache(true, 3600, 'list_notification_by_user_' . $user->getId())
            ->getResult();

    }

}
