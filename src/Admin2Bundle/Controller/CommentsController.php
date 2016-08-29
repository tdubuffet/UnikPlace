<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/08/16
 * Time: 09:43
 */

namespace Admin2Bundle\Controller;

use CommentBundle\Entity\Comment;
use CommentBundle\Event\CommentEvent;
use CommentBundle\Event\CommentEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CommentsController
 * @package Admin2Bundle\Controller
 * @Route("/comments")
 */
class CommentsController extends Controller
{
    /**
     * @Route("/", name="ad2_comments_list")
     * @return Response
     */
    public function commentListAction()
    {
        $comments = $this->getDoctrine()->getRepository('CommentBundle:Comment')
            ->findBy(['isValidated' => false, 'isDeleted' => false]);

        return $this->render("Admin2Bundle:Comments:list.html.twig", ['comments' => $comments]);
    }

    /**
     * @Route("/commentaires-ajax", name="ad2_comment_ajax", options={"expose": true})
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
        if (!in_array($request->request->get('action'), ['save', 'get', 'validate'])) {
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

        if ($request->request->get('action') == "validate") {
            $comment->setIsValidated(true)->setIsDeleted(false);
            $dispatcher = $this->get('event_dispatcher');
            if ($comment->getParent()) {
                $dispatcher->dispatch(CommentEvents::PRODUCT_COMMENT_REPLY, new CommentEvent($comment));
            } else {
                $dispatcher->dispatch(CommentEvents::PRODUCT_COMMENT, new CommentEvent($comment));
            }

            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('Commentaire %s supprimée', $comment->getId()));

            return new JsonResponse(['message' => sprintf('Commentaire %s accepté', $comment->getId())]);
        }

        return new JsonResponse(['message' => 'action parameter not valid'], 409);
    }
}