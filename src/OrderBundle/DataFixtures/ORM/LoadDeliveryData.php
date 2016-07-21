<?php

namespace OrderBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OrderBundle\Entity\Delivery;
use ProductBundle\Entity\Status;

class LoadDeliveryData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableDelivery = [
            ['name' => 'Remise en main propre',    'code' => 'by_hand'],
            ['name' => 'Lettre suivie',   'code' => 'tracked_letter'],
            ['name' => 'Colis',   'code' => 'parcel'],
        ];

        foreach ($availableDelivery as $statusInfos) {
            $delivery = new Delivery();
            $delivery->setName($statusInfos['name']);
            $delivery->setCode($statusInfos['code']);
            $manager->persist($delivery);
        }
        $manager->flush();
    }
}