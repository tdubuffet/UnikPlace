<?php

namespace CommentBundle\Controller;

use CommentBundle\Entity\Comment;
use CommentBundle\Form\CommentType;
use ProductBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/comment")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/thread/{id}", name="comment_thread_product")
     * @Template("CommentBundle:Default:index.html.twig")
     */
    public function threadAction(Request $request, Product $product)
    {

        $views = [];

        $thread = $this->getDoctrine()->getRepository('CommentBundle:ThreadComment')->findOneByProduct($product);

        if ($thread) {
            $views['comments'] = $this->getDoctrine()->getRepository('CommentBundle:Comment')->findBy([
                'thread'        => $thread,
                'parent'        => null,
                'isValidated'   => true,
                'isDeleted'     => false
            ]);
        }

        // New comment
        if ($this->getUser() && $this->getUser() != $product->getUser()) {

            $form = $this->createForm(CommentType::class);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('comment.manager')->newComment(
                    $thread,
                    $product,
                    $form->getData(),
                    $this->getUser()
                );
            }
            $views['formNewComment'] = $form->createView();
        }

        //Reply
        if ($this->getUser() && $this->getUser() == $product->getUser() && $request->get('comment_id')) {

            $parent = $this->getDoctrine()->getRepository('CommentBundle:Comment')->find($request->get('comment_id'));
            if ($parent && $request->get('message')){

                $comment = new Comment();
                $comment->setMessage($request->get('message'));
                $comment->setParent($parent);

                $this->get('comment.manager')->newComment(
                    $thread,
                    $product,
                    $comment,
                    $this->getUser()
                );
            }
        }


        return array_merge($views, [
            'thread' => $thread,
            'product' => $product
        ]);
    }
}
