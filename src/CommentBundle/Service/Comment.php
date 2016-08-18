<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 18/08/16
 * Time: 14:00
 */

namespace CommentBundle\Service;


use CommentBundle\Entity\ThreadComment;
use Doctrine\ORM\EntityManager;
use MessageBundle\Entity\Thread;
use ProductBundle\Entity\Product;

class Comment
{

    private $entityManager;

    public function __construct(EntityManager $em)
    {

        $this->entityManager = $em;

    }

    /**
     * add new comment to thread
     *
     * @param ThreadComment $thread
     * @param Product $product
     * @param \CommentBundle\Entity\Comment $comment
     * @param $user
     * @return \CommentBundle\Entity\Comment
     */
    public function newComment($thread, Product $product, \CommentBundle\Entity\Comment $comment, $user)
    {

        if ($thread == null) {
            $thread = $this->generateThread($product);
        }

        $comment->setUser($user);
        $comment->setIsValidated(false);
        $comment->setIsDeleted(false);
        $comment->setThread($thread);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }

    private function generateThread($product)
    {
        $thread = new ThreadComment();
        $thread->setProduct($product);

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        return $thread;

    }

}