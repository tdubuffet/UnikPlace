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
 * @Route("/admin")
 */
class AdminController extends Controller
{

    /**
     * @Route("/commentaires", name="admin_comments")
     * @Template("CommentBundle:Admin:list.html.twig")
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request)
    {
        $comments = $this->getDoctrine()->getRepository('CommentBundle:Comment')->findBy([
            'isValidated'   => false,
            'isDeleted'     => false
        ]);

        if ($request->get('save-comment')) {
            foreach($comments as $comment) {

                if ($val = $request->get('comment_' . $comment->getId())) {

                    if ($val = 1) {
                        $comment->setIsValidated(true);
                        $comment->setIsDeleted(false);
                    }

                    if ($val = 0) {
                        $comment->setIsValidated(false);
                        $comment->setIsDeleted(true);
                    }

                    $this->getDoctrine()->getManager()->persist($comment);

                }

            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_comments');
        }

        return [
            'comments' => $comments
        ];
    }

}
