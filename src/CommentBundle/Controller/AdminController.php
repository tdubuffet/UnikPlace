<?php

namespace CommentBundle\Controller;

use CommentBundle\Entity\Comment;
use CommentBundle\Event\CommentEvent;
use CommentBundle\Event\CommentEvents;
use CommentBundle\Form\CommentType;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $comments = $this->getDoctrine()->getRepository('CommentBundle:Comment')
            ->findBy(['isValidated' => false, 'isDeleted' => false]);

        if ($request->get('save-comment')) {
            foreach ($comments as $comment) {

                if ($val = $request->get('comment_'.$comment->getId())) {
                    if ($val = 1) {
                        $comment->setIsValidated(true);
                        $comment->setIsDeleted(false);
                    }

                    if ($val = 0) {
                        $comment->setIsValidated(false);
                        $comment->setIsDeleted(true);
                    }

                    if ($comment->getParent()) {
                        $this->get('event_dispatcher')
                            ->dispatch(CommentEvents::PRODUCT_COMMENT_REPLY, new CommentEvent($comment));
                    } else {
                        $this->get('event_dispatcher')
                            ->dispatch(CommentEvents::PRODUCT_COMMENT, new CommentEvent($comment));
                    }

                    $this->getDoctrine()->getManager()->persist($comment);
                }
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_comments');
        }

        return ['comments' => $comments,];
    }

    /**
     * @Route("/commentaires-ajax", name="ajax_admin_comment", options={"expose": true})
     * @param Request $request
     * @Method("POST")
     * @return JsonResponse
     */
    public function commentAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['message' => 'You must be authentificated to update the comment.'], 401);
        }

        if (!$request->request->has('comment_id')) {
            return new JsonResponse(['message' => 'comment_id parameter missing'], 409);
        }
        if (!in_array($request->request->get('action'), ['save', 'get'])) {
            return new JsonResponse(['message' => 'action parameter not valid or missing'], 409);
        }
        $id = $request->request->get('comment_id');
        $comment = $this->getDoctrine()->getRepository("CommentBundle:Comment")->findOneBy(['id' => $id]);

        if (!$comment) {
            return new JsonResponse(['message' => sprintf('No comment found for id %s', $id)], 404);
        }

        if ($request->request->get('action') == 'get') {
            return new JsonResponse(['comment' => ['message' => $comment->getMessage(), 'id' => $comment->getId()]]);
        }

        if ($request->request->get('action') == 'save') {
            if (!$request->request->has('comment_message')) {
                return new JsonResponse(['message' => 'comment_message parameter missing'], 409);
            }

            $comment->setMessage($request->request->get('comment_message'));
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse(['comment' => ['message' => $comment->getMessage(), 'id' => $comment->getId()]]);
        }

        return new JsonResponse(['message' => 'action parameter not valid'], 409);
    }

}
