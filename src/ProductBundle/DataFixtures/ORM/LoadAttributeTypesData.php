<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\AttributeType;

class LoadAttributeTypesData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableTypes = ['string', 'boolean', 'integer', 'float'];
        foreach ($availableTypes as $name) {
            $type = new AttributeType();
            $type->setName($name);
            $manager->persist($type);
        }
        $manager->flush();
    }
}