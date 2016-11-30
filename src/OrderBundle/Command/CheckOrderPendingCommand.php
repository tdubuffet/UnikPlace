<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 10:18
 */

namespace OrderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\LogicException;

/**
 * Class CheckOrderCommand
 * @package OrderBundle\Command
 */
class CheckOrderPendingCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("check:order_pending")
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
        $orders = $manager->getRepository("OrderBundle:Order")->findBy(['status' => 1]);
        $logger = $this->getContainer()->get('monolog.logger.cron');

        foreach ($orders as $order) {
            if ($order->getCreatedAt() < new \DateTime("-2days")) {
                $this->getContainer()->get('order_service')->cancelOrder($order);
                $logger->addNotice(sprintf("Order %s canceled from cron %s", $order->getId(), __CLASS__));
            }
        }
        $orders = $manager->getRepository("OrderBundle:Order")->findBy(['status' => 2]);
        foreach ($orders as $order) {
            if ($order->getCreatedAt() < new \DateTime("-30days") && $order->getEmc() == false) {
                $this->getContainer()->get('order_service')->disputeOrder($order);
                $logger->addNotice(sprintf("Order %s validated from cron %s", $order->getId(), __CLASS__));
            }

            $track = $order->getEmcTracking();
            if ($order->getEmc() == true && isset($track['etat']) && $track['etat'] == "LIV" && $order->getCreatedAt() < new \DateTime("-15days")) {
                $this->getContainer()->get('order_service')->validateOrder($order);
                $logger->addNotice(sprintf("Order %s validated EMC from cron %s", $order->getId(), __CLASS__));
            }
        }

        $manager->flush();
    }
}