<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 18/08/16
 * Time: 14:00
 */

namespace CommentBundle\Service;


use CommentBundle\Entity\ThreadComment;
use CommentBundle\Form\CommentType;
use Doctrine\ORM\EntityManager;
use ProductBundle\Entity\Product;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class Comment
{

    private $entityManager;
    private $router;
    private $formFactory;

    public function __construct(EntityManager $em, Router $router, FormFactory $formFactory)
    {

        $this->entityManager    = $em;
        $this->formFactory      = $formFactory;
        $this->router           = $router;

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

    public function handler(Request $request, Product $product, $user)
    {
        $views = [];
        $thread = $this->entityManager->getRepository('CommentBundle:ThreadComment')->findOneBy(['product' => $product]);

        if ($thread) {
            $views['comments'] = $this->entityManager->getRepository('CommentBundle:Comment')->findBy([
                'thread'        => $thread,
                'parent'        => null,
                'isValidated'   => true,
                'isDeleted'     => false
            ]);
        }

        // New comment
        if ($user && $user != $product->getUser()) {

            $form = $this->formFactory->create(CommentType::class); // A faire
            $form->handleRequest($request);
            if ($form->isValid()) {
                $comment = $this->newComment($thread,$product,$form->getData(),$user);

                return new RedirectResponse(
                    $this->router->generate('product_details', [
                        'id' => $product->getId(),
                        'slug' => $product->getSlug()
                    ]) . '#comment-' . $comment->getThread()->getId() . $comment->getId()
                );
            }
            $views['formNewComment'] = $form->createView();
        }

        //Reply
        if ($user && $user == $product->getUser() && $request->get('comment_id')) {

            $parent = $this->entityManager->getRepository('CommentBundle:Comment')->find($request->get('comment_id'));
            if ($parent && $request->get('message')){

                $comment = new \CommentBundle\Entity\Comment();
                $comment->setMessage($request->get('message'));
                $comment->setParent($parent);

                $comment = $this->newComment($thread,$product,$comment,$user);

                return new RedirectResponse(
                    $this->router->generate('product_details', [
                        'id' => $product->getId(),
                        'slug' => $product->getSlug()
                    ]) . '#comment-' . $thread->getId() . $comment->getId()
                );
            }
        }

        return array_merge($views, ['thread' => $thread,'product' => $product]);
    }

}