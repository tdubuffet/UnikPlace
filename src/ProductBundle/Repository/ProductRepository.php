<?php

namespace ProductBundle\Repository;

use ProductBundle\Entity\Collection;
use ProductBundle\Entity\Currency;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\Status;
use UserBundle\Entity\User;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Product $product
     * @param $limit
     * @return array
     */
    public function findSimilarProducts(Product $product, $limit)
    {
        $total = $this->countSimilarProducts($product);

        $offset = rand(0, $total) - $limit;
        $offset = $offset < 0 ? 0 : $offset;

        $results = $this->createQueryBuilder('p')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->where('p.id != :id')
            ->andWhere('p.category = :category_id')
            ->andWhere('p.status = :status')
            ->setParameters(['id' => $product->getId(), 'category_id' => $product->getCategory(), 'status' => 2])
            ->getQuery()
            ->getResult();

        if (count($results) > 0) {
            shuffle($results);
        }

        return $results;
    }

    /**
     * @param Product $product
     * @param $limit
     * @return array
     */
    public function findProductsByUser(Product $product, $limit = 8)
    {

        $results = $this->createQueryBuilder('p')
            ->setMaxResults($limit)
            ->where('p.id != :id')
            ->andWhere('p.status = :status')
            ->andWhere('p.user = :user')
            ->setParameters([
                'id' => $product->getId(),
                'status' => 2,
                'user' => $product->getUser()
            ])
            ->getQuery()
            ->getResult();

        if (count($results) > 0) {
            shuffle($results);
        }

        return $results;
    }

    /**
     * @param Product $product
     * @return int
     */
    public function countSimilarProducts($product)
    {
        return count($this->findBy(['category' => $product->getCategory()])) - 1;
    }

    /**
     * @param User $user
     * @param array $status
     * @return array
     * @throws \Exception
     */
    public function findForUserAndStatus(User $user, array $status)
    {
        if (count($status) != 3) {
            throw new \Exception("status must be an array with 3 fields");
        }
        $params = ['user' => $user, 'status1' => $status[0], 'status2' => $status[1], 'status3' => $status[1]];

        return $this->createQueryBuilder("q")
            ->orWhere("q.status = :status1")
            ->orWhere("q.status = :status2")
            ->orWhere("q.status = :status3")
            ->andWhere("q.user = :user")
            ->setParameters($params)
            ->addOrderBy('q.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public function findByValidStatusAndCollection(Collection $collection)
    {
        $params = [
            'collection' => $collection,
            'status1' => "published",
            'status2' => "sold",
            'status3' => "unavailable"
        ];

        return $this->createQueryBuilder("q")
            ->leftJoin('q.collections', 'collections')
            ->leftJoin('q.status', 'status')
            ->where('status.name = :status1')
            ->orWhere('status.name = :status2')
            ->orWhere('status.name = :status3')
            ->andWhere('collections.id = :collection')
            ->setParameters($params)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Status $status
     * @return int
     */
    public function countByStatus(Status $status)
    {
        return count($this->findBy(['status' => $status]));
    }
    /**
     * @param Currency $currency
     * @return int
     */
    public function countByCurrency(Currency $currency)
    {
        return count($this->findBy(['currency' => $currency]));
    }

    public function count($statusName = null)
    {

        $query = $this->createQueryBuilder('p')
            ->select('count(p.id)');

        if ($statusName) {
            $query->join('p.status', 's')
                ->where('s.name = :statusName');
            $query->setParameter('statusName', $statusName);
        }


        return $query
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
