<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\AttributeSearchTemplate;

class LoadAttributeSearchTemplatesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $templates = ['text', 'number', 'range', 'select', 'color', 'multiselect', 'select2', 'multiselect2'];
        foreach ($templates as $templateName) {
            $template = new AttributeSearchTemplate();
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
        return 2;
    }
}