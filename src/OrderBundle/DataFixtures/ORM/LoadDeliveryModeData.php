<?php

namespace OrderBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OrderBundle\Entity\DeliveryMode;
use ProductBundle\Entity\Status;

class LoadDeliveryModeData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableDeliveryModes = [
            ['name' => 'Retrait au domicile du vendeur', 'code' => 'by_hand',                  'type' => 'by_hand'],
            ['name' => 'Transport géré par le vendeur',  'code' => 'seller_custom',            'type' => 'shipping'],
            ['name' => 'Lettre suivie Colissimo',        'code' => 'colissimo_tracked_letter', 'type' => 'shipping'],
            ['name' => 'Colissimo',                      'code' => 'colissimo_parcel',         'type' => 'shipping'],
        ];

        foreach ($availableDeliveryModes as $statusInfos) {
            $deliveryMode = new DeliveryMode();
            $deliveryMode->setName($statusInfos['name']);
            $deliveryMode->setCode($statusInfos['code']);
            $deliveryMode->setType($statusInfos['type']);
            $manager->persist($deliveryMode);
        }
        $manager->flush();
    }
}