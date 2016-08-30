<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 30/08/16
 * Time: 18:00
 */

namespace FOS2CommentBundle\Entity;

use Doctrine\ORM\EntityManager;
use FOS\CommentBundle\Model\CommentManager as BaseCommentManager;
use FOS\CommentBundle\Model\ThreadInterface;
use FOS\CommentBundle\Model\CommentInterface;
use FOS\CommentBundle\Sorting\SortingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Default ORM CommentManager.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class CommentManager extends \FOS\CommentBundle\Entity\CommentManager
{

    /**
     * {@inheritdoc}
     */
    public function findCommentsByThread(ThreadInterface $thread, $depth = null, $sorterAlias = null)
    {
        $qb = $this->repository
            ->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->join('c.author', 'a')
            ->where('t.id = :thread')
            ->orderBy('c.ancestors', 'ASC')
            ->setParameter('thread', $thread->getId());

        if (null !== $depth && $depth >= 0) {
            // Queries for an additional level so templates can determine
            // if the final 'depth' layer has children.

            $qb->andWhere('c.depth < :depth')
                ->setParameter('depth', $depth + 1);
        }

        $comments = $qb
            ->getQuery()
            ->execute();

        if (null !== $sorterAlias) {
            $sorter = $this->sortingFactory->getSorter($sorterAlias);
            $comments = $sorter->sortFlat($comments);
        }

        return $comments;
    }
}
