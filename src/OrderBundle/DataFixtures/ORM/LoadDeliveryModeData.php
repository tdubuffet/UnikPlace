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
            [
                'name' => 'Retrait au domicile du vendeur',
                'code' => 'by_hand',
                'type' => 'by_hand',
                'description' => "Vous vous déplacez chez le vendeur ou convenez d'un rendez-vous dans un lieu public
                avec lui pour la remise du produit.",
            ],
            [
                'name' => 'Transport géré par le vendeur',
                'code' => 'seller_custom',
                'type' => 'shipping',
                'description' => "Le produit vous est livré par le transporteur choisi par le vendeur.",
            ],
            [
                'name' => 'Lettre suivie Colissimo',
                'code' => 'colissimo_tracked_letter',
                'type' => 'shipping',
                'description' => "Le produit est expédié par le vendeur en lettre suivie via Colissimo.",
            ],
            [
                'name' => 'Colissimo',
                'code' => 'colissimo_parcel',
                'type' => 'shipping',
                'description' => "Le produit est expédié par le vendeur via Colissimo.",
            ],
        ];

        foreach ($availableDeliveryModes as $statusInfos) {
            $deliveryMode = new DeliveryMode();
            $deliveryMode->setName($statusInfos['name']);
            $deliveryMode->setCode($statusInfos['code']);
            $deliveryMode->setType($statusInfos['type']);
            $deliveryMode->setDescription($statusInfos['description']);
            $manager->persist($deliveryMode);
        }
        $manager->flush();
    }
}