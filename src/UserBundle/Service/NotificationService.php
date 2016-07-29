<?php
namespace UserBundle\Service;

use Doctrine\ORM\EntityManager;
use MangoPay\Libraries\Exception;
use UserBundle\Entity\Notification;
use UserBundle\Entity\User;

/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 28/07/16
 * Time: 16:14
 */
class NotificationService
{

    private $entityManager;

    private $notificationParams;

    public function __construct(EntityManager $entityManager, $notificationsParams)
    {
        $this->notificationParams = $notificationsParams;

        $this->entityManager = $entityManager;
    }


    public function createNotification(User $user, $code, $data)
    {

        if (!isset($this->notificationParams[$code])) {
            throw new \Exception('Not found notification');
        }

        if ($this->checkData($code, $data) == true) {
            $notification = new Notification();
            $notification->setRead(false);
            $notification->setUser($user);
            $notification->setCode($code);
            $notification->setData($data);

            $this->invalidateCache($user->getId());

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }


    }

    public function readNotification(Notification $notification)
    {
        $notification->setRead(true);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $this->invalidateCache($notification->getUser()->getId());
    }

    /**
     * @param $code
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function checkData($code, $data)
    {
        if (!isset($this->notificationParams[$code])) {
            throw new \Exception('Not found notification');
        }


        $params = $this->notificationParams[$code]['params'];

        foreach($params as $p) {
            if (!isset($data[$p])) {
                throw new \Exception('Not found mandatory param: ' . $p);
            }
        }

        return true;
    }

    public function notifications($notifications)
    {
        $notificationsTransformers = [];

        foreach($notifications as $notification) {
            $notificationsTransformers[] =$this->notification($notification);
        }

        return $notificationsTransformers;
    }

    public function notification(Notification $notification)
    {
        if (!isset($this->notificationParams[$notification->getCode()])) {
            throw new \Exception('Not found notification');
        }

        $notificationParams = $this->notificationParams[$notification->getCode()];

        return $this->generateMessage($notificationParams['message'], $notification);


    }

    public function generateMessage($message, Notification $notification)
    {

        foreach($notification->getData() as $key => $data) {
            $message = str_replace("#$key#", $data, $message);
        }

        $notification->setMessage($message);

        return $notification;
    }

    public function getRouteParams(Notification $notification)
    {
        if (!isset($this->notificationParams[$notification->getCode()])) {
            throw new \Exception('Not found notification');
        }

        $params = [];
        $data = $notification->getData();
        $notificationParams = $this->notificationParams[$notification->getCode()];

        if (isset($notificationParams['routeParams'])) {
            foreach($notificationParams['routeParams'] as $key => $routeP) {
                if (isset($data[$routeP])) {
                    $params[$key] = $data[$routeP];
                }
            }
        }

        return [
            'route' => $notificationParams['route'],
            'params' => $params
        ];

    }

    public function invalidateCache($id)
    {
        $this->entityManager->getConfiguration()->getResultCacheImpl()->delete('list_notification_by_user_' . $id);
        $this->entityManager->getConfiguration()->getResultCacheImpl()->delete('count_notification_by_user_' . $id);
    }

}