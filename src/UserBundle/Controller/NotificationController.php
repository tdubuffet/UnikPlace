<?php

namespace UserBundle\Controller;

use OrderBundle\Entity\Order;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UserBundle\Entity\Notification;
use UserBundle\Form\PreferenceFormType;
use UserBundle\Form\RatingType;

/**
 * Class NotificationController
 * @package UserBundle\Controller
 *
 */
class NotificationController extends Controller
{

    /**
     * @Template("UserBundle:Notification:bar.html.twig")
     */
    public function notificationBarAction()
    {

        $notifications = [];
        $count = 0;

        if ($this->getUser()) {
            $notifications = $this->getDoctrine()->getRepository('UserBundle:Notification')->getLastNotificationByUserCache(
                $this->getUser()
            );

            $count = $this->getDoctrine()->getRepository('UserBundle:Notification')->countNotificationUnreadByUserCache(
                $this->getUser()
            );

            $notifications = $this->get('user.notification')->notifications($notifications);
        }

        return [
            'notifications' => $notifications,
            'count' => $count
        ];
    }

    /**
     * @Route("/notification/{id}", name="user_notification_request")
     */
    public function routerNotificationAction(Request $request, Notification $notification)
    {
        $routeParams = $this->get('user.notification')->getRouteParams($notification);

        if ($notification->getRead() == false) {

            $this->get('user.notification')->readNotification($notification);

        }

        return $this->redirectToRoute($routeParams['route'], $routeParams['params']);

    }

    /**
     * @Route("/compte/notifications", name="user_notification_list")
     * @Template("UserBundle:Notification:list.html.twig")
     */
    public function listAction(Request $request)
    {

        $query = $this->getDoctrine()->getRepository('UserBundle:Notification')->findAllByUser($this->getUser());

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'results' => $pagerfanta
        ];
    }

}
