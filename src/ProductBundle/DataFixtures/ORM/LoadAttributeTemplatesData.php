<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\AttributeTemplate;

class LoadAttributeTemplatesData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $templates = ['text', 'number', 'range', 'select', 'color', 'multiselect'];
        foreach ($templates as $templateName) {
            $template = new AttributeTemplate();
            $template->setName($templateName);
            $manager->persist($template);
        }
        $manager->flush();
    }
}