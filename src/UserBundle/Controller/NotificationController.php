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
 * @Route("/notification")
 */
class NotificationController extends Controller
{

    /**
     * @Route("/bar", name="user_notification_bar")
     * @Template("UserBundle:Notification:bar.html.twig")
     */
    public function notificationBarAction()
    {

        $notifications = [];

        if ($this->getUser()) {
            $notifications = $this->getDoctrine()->getRepository('UserBundle:Notification')->getLastNotificationByUserCache(
                $this->getUser()
            );

            $notifications = $this->get('user.notification')->notifications($notifications);
        }

        return [
            'notifications' => $notifications
        ];
    }

    /**
     * @Route("/{id}", name="user_notification_request")
     */
    public function routerNotificationAction(Request $request, Notification $notification)
    {
        $routeParams = $this->get('user.notification')->getRouteParams($notification);

        if ($notification->getRead() == false) {

            $this->get('user.notification')->readNotification($notification);

        }

        return $this->redirectToRoute($routeParams['route'], $routeParams['params']);

    }

}
