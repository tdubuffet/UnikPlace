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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Exception\LogicException;

/**
 * Class CheckOrderCommand
 * @package OrderBundle\Command
 */
class CategoryTranslationCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("trans:category")
            ->setDescription("Traduction des catégories");
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

        $categories = $manager->getRepository('ProductBundle:Category')->findAll();

        foreach ($categories as $category) {
            if ($category->translate('fr', null)->getName() == null) {

                $translation = new CategoryTranslation();
                $translation->setName($category->getName());
                $translation->setLocale('fr');
                $category->addTranslation($translation);

                $output->writeln('Traduction - FR : ' . $category->getName());
            }

            if ($category->translate('en', null)->getName() == null) {

                $name = Translation::trans($category->getName(), 'en');
                $translation = new CategoryTranslation();
                $translation->setName($name);
                $translation->setLocale('en');
                $category->addTranslation($translation);


                $output->writeln('Traduction - EN : ' . $name);
            }

            $manager->persist($category);
        }

        $manager->flush();

    }

}