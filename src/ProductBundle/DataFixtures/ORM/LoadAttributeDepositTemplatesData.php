<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\AttributeDepositTemplate;

class LoadAttributeDepositTemplatesData implements FixtureInterface
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
}