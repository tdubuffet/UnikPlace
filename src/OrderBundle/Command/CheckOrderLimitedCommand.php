<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 10:18
 */

namespace OrderBundle\Command;

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
class CheckOrderLimitedCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("check:order_limited")
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
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $orders = $manager->getRepository("OrderBundle:Order")->findBy(['status' => 7]);
        $logger = $this->getContainer()->get('monolog.logger.cron');
        $mangopay = $this->getContainer()->get('mangopay_service')->getMangoPayApi();

        foreach ($orders as $order) {

            $user = $mangopay->Users->Get($order->getUser()->getMangopayUserId());

            if ($user->KYCLevel == 'REGULAR') {

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

        $manager->flush();
    }
}