<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 10:18
 */

namespace UserBundle\Command;

use OrderBundle\Event\OrderEvent;
use OrderBundle\Event\OrderEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\LogicException;

/**
 * Class CheckOrderCommand
 * @package OrderBundle\Command
 */
class CheckUserLimitedCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("check:user:limited")
            ->setDescription("Check pending order > 48h and accepted order > 15 days - To run every 24h");
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->limitedBuyer($input, $output);
        $this->limitedSeller($input, $output);
    }


    public function limitedSeller(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $logger = $this->getContainer()->get('monolog.logger.cron');
        $mangopay = $this->getContainer()->get('mangopay_service')->getMangoPayApi();


        $users  = $manager->getRepository('UserBundle:User')->findBy([
            'limitedBuyer' => true
        ]);

        foreach($users as $user) {


            $userMangoPay = $mangopay->Users->Get($user->getMangopayUserId());

            if ($userMangoPay->KYCLevel == 'REGULAR') {


                $this->getContainer()->get('mailer_sender')->sendKYCValidatedEmailMessageCron($user);

                $user->setLimitedSeller(false);

                $logger->addNotice(sprintf("User %s limited regualer from cron %s", $user->getUsername(), __CLASS__));
            }


            $manager->persist($user);
        }


        $manager->flush();
    }

    public function limitedBuyer(InputInterface $input, OutputInterface $output)
    {

        $manager = $this->getContainer()->get('doctrine')->getManager();
        $logger = $this->getContainer()->get('monolog.logger.cron');
        $mangopay = $this->getContainer()->get('mangopay_service')->getMangoPayApi();


        $users  = $manager->getRepository('UserBundle:User')->findBy([
            'limitedBuyer' => true
        ]);

        foreach($users as $user) {

            $userMangoPay = $mangopay->Users->Get($user->getMangopayUserId());

            $regular = false;

            if ($userMangoPay->KYCLevel == 'REGULAR') {

                $regular = true;

                $this->getContainer()->get('mailer_sender')->sendKYCValidatedEmailMessageCron($user);

                $user->setLimitedBuyer(false);


                $logger->addNotice(sprintf("User %s limited regualer from cron %s", $user->getUsername(), __CLASS__));

            }

            $orders = $manager->getRepository("OrderBundle:Order")->findBy([
                'status' => 7,
                'user' => $user
            ]);

            foreach ($orders as $order) {
                if ($regular) {

                    $event = new OrderEvent($order);
                    $this->getContainer()->get('event_dispatcher')->dispatch(OrderEvents::ORDER_CREATED, $event);


                    $order->setStatus(
                        $manager->getRepository('OrderBundle:Status')->findOneByName('pending')
                    );

                    $manager->persist($order);
                    $logger->addNotice(sprintf("Order %s pending from cron %s", $order->getId(), __CLASS__));

                } elseif ($order->getCreatedAt() < new \DateTime("-7days")) {
                    $this->getContainer()->get('order_service')->cancelOrder($order);
                    $logger->addNotice(sprintf("Order %s canceled from cron %s", $order->getId(), __CLASS__));
                }
            }

            $manager->persist($user);

        }

        $manager->flush();
    }
}