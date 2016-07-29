<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 11:35
 */

namespace OrderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\LogicException;

/**
 * Class RecallOrderCommand
 * @package OrderBundle\Command
 */
class RecallOrderPendingCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('recall:order_pending')->setDescription("Send recall mail for commands- To run every 12h");
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
        $orders = $manager->getRepository("OrderBundle:Order")->findBy(['status' => 1]);
        $mailer = $this->getContainer()->get('mailer_sender');

        foreach ($orders as $order) {
            if ($order->getCreatedAt() > new \DateTime("-2days")) {
                $mailer->sendPendingOrderToSellerEmailMessage($order);
            }
        }
    }
}