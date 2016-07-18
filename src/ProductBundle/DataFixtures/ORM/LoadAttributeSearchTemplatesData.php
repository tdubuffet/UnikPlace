<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\AttributeSearchTemplate;

class LoadAttributeSearchTemplatesData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $templates = ['text', 'number', 'range', 'select', 'color', 'multiselect'];
        foreach ($templates as $templateName) {
            $template = new AttributeSearchTemplate();
            $template->setName($templateName);
            $manager->persist($template);
        }
        $manager->flush();
    }
}