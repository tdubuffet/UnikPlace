<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 26/08/16
 * Time: 15:12
 */

namespace Admin2Bundle\Utils;


use CommentBundle\Event\CommentEvent;
use CommentBundle\Event\CommentEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class DeleteService
{
    /** @var EntityManager $em */
    private $em;
    /** @var AuthorizationChecker $checker */
    private $checker;
    /** @var TraceableEventDispatcher $dispatcher */
    private $dispatcher;

    /**
     * DeleteService constructor.
     * @param EntityManager $entityManager
     * @param AuthorizationChecker $checker
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorizationChecker $checker,
        TraceableEventDispatcher $dispatcher
    ) {
        $this->em = $entityManager;
        $this->checker = $checker;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function remove(Request $request)
    {
        if (!$this->checker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return [['message' => 'You must be authentificated to remove a collection.'], 401];
        }

        if (!$request->request->has('id')) {
            return [['message' => 'Parameter id missing.'], 401];
        }

        if (!$request->request->has('type')) {
            return [['message' => 'Parameter type missing.'], 401];
        }

        $id = $request->request->get('id');
        switch ($request->request->get('type')) {
            case "collection" :
                $collection = $this->em->getRepository("ProductBundle:Collection")->findOneBy(['id' => $id]);
                if (!$collection) {
                    return [['message' => 'Collection not found'], 404];
                }

                $this->em->remove($collection->getImage());
                $this->em->remove($collection);
                $message = sprintf('Tendance %s supprimée', $collection->getName());
                $this->addFlash("success", $message);
                break;
            case "category":
                $category = $this->em->getRepository("ProductBundle:Category")->findOneBy(['id' => $id]);
                if (!$category) {
                    return [['message' => 'Category not found'], 404];
                }

                $this->em->remove($category);
                $message = sprintf('Catégorie %s supprimée', $category->getName());
                $this->addFlash("success", $message);
                break;
            case 'order_status' :
                $status = $this->em->getRepository("OrderBundle:Status")->findOneBy(['id' => $id]);
                if (!$status) {
                    return [['message' => 'Order Status not found'], 404];
                }
                $this->em->remove($status);
                $message = sprintf('Status de commande %s supprimé', $status->getName());
                $this->addFlash("success", $message);
                break;
            case 'product_status' :
                $status = $this->em->getRepository("ProductBundle:Status")->findOneBy(['id' => $id]);
                if (!$status) {
                    return [['message' => 'Product Status not found'], 404];
                }
                $this->em->remove($status);
                $message = sprintf('Status de produit %s supprimé', $status->getName());
                $this->addFlash("success", $message);
                break;
            case 'currency' :
                $currency = $this->em->getRepository("ProductBundle:Currency")->findOneBy(['id' => $id]);
                if (!$currency) {
                    return [['message' => 'Currency not found'], 404];
                }
                $this->em->remove($currency);
                $message = sprintf('Devise %s supprimée', $currency->getCode());
                $this->addFlash("success", $message);
                break;
            case 'comment':
                $comment = $this->em->getRepository("CommentBundle:Comment")->findOneBy(['id' => $id]);
                if (!$comment) {
                    return [['message' => 'Comment not found'], 404];
                }
                $comment->setIsValidated(false)->setIsDeleted(true);

                if ($comment->getParent()) {
                    $this->dispatcher->dispatch(CommentEvents::PRODUCT_COMMENT_REPLY, new CommentEvent($comment));
                } else {
                    $this->dispatcher->dispatch(CommentEvents::PRODUCT_COMMENT, new CommentEvent($comment));
                }
                $this->em->persist($comment);
                $message = sprintf('Commentaire %s supprimé', $comment->getId());
                $this->addFlash("success", $message);
                break;
            default:
                return [['message' => 'Parameter type invalid.'], 401];
                break;
        }
        $this->em->flush();

        return [['message' => $message], 200];
    }

    /**
     * @param $type
     * @param $message
     */
    private function addFlash($type, $message)
    {
        $session = new Session();
        $session->getFlashBag()->add($type, $message);
    }

}