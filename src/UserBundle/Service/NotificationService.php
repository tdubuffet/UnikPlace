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

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }


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

}