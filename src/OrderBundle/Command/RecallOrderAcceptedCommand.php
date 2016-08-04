<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 11:50
 */

namespace OrderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\LogicException;

/**
 * Class RecallSendCommand
 * @package OrderBundle\Command
 */
class RecallOrderAcceptedCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("recall:order_accepted")->setDescription("Check accepted command- To run every 48h");
    }

    /**
     * Executes the current command.
     *
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
        $orders = $manager->getRepository("OrderBundle:Order")->findBy(['status' => 2]);
        $mailer = $this->getContainer()->get('mailer_sender');
        $logger = $this->getContainer()->get('monolog.logger.cron');

        foreach ($orders as $order) {
            if ($order->getCreatedAt() < new \DateTime("-5days") && $order->getCreatedAt() > new \DateTime("-15days")) {
                $mailer->sendAcceptedOrderToSellerEmailMessage($order);
                $email = $order->getProduct()->getUser()->getEmail();
                $logger->addNotice(sprintf("Sending recall_order_accepted email to %s from cron %s", $email, __CLASS__));
            }
        }
    }

}