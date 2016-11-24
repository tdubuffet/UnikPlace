<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 10:18
 */

namespace ProductBundle\Command;

use AppBundle\Service\Translation;
use OrderBundle\Event\OrderEvent;
use OrderBundle\Event\OrderEvents;
use ProductBundle\Entity\CategoryTranslation;
use ProductBundle\Entity\ProductTranslation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\LogicException;

/**
 * Class CheckOrderCommand
 * @package OrderBundle\Command
 */
class ProductTranslationCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("trans:product")
            ->setDescription("Traduction des produits");
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

        $products = $manager->getRepository('ProductBundle:Product')->findAll();

        foreach ($products as $product) {
            if ($product->translate('fr', null)->getName() == null) {

                $translation = new ProductTranslation();
                $translation->setName($product->getName());
                $translation->setDescription($product->getDescription());
                $translation->setLocale('fr');
                $product->addTranslation($translation);

                $output->writeln('Traduction - FR : ' . $product->getName());
            }

            if ($product->translate('en', null)->getName() == null) {

                $name = Translation::trans($product->getName(), 'en');
                $description = Translation::trans($product->getName(), 'en');
                $translation = new ProductTranslation();
                $translation->setName($name);
                $translation->setDescription($description);
                $translation->setLocale('en');
                $product->addTranslation($translation);


                $output->writeln('Traduction - EN : ' . $name);
            }

            $manager->persist($product);
        }

        $manager->flush();

    }

}