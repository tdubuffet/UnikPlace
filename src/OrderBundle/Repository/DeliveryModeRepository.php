<?php

namespace OrderBundle\Repository;

/**
 * DeliveryModeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeliveryModeRepository extends \Doctrine\ORM\EntityRepository
{

    public function findAllCode()
    {
        return $this->createQueryBuilder('d')
            ->select('d.code')
            ->getQuery()
            ->getArrayResult();
    }

}
