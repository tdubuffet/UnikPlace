<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\AttributeDepositTemplate;

class LoadAttributeDepositTemplatesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $templates = ['text', 'number', 'select', 'select2', 'color'];
        foreach ($templates as $templateName) {
            $template = new AttributeDepositTemplate();
            $template->setName($templateName);
            $manager->persist($template);
        }
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}