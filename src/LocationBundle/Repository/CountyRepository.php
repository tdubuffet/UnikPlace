<?php

namespace LocationBundle\Repository;

use Doctrine\ORM\Query;

/**
 * CountyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CountyRepository extends \Doctrine\ORM\EntityRepository
{

    public function findAllToArray()
    {
        return $this->createQueryBuilder("q")->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
