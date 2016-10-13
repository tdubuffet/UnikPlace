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
                'description' => "Vous vous déplacez chez le vendeur ou convenez d''un rendez-vous dans un lieu 
                public avec lui pour la remise du produit. En plus de la messagerie privée, pour convenir d'un 
                rendez-vous, nous vous communiquerons son numéro de téléphone une fois la commande validée.",
                'emc' => false,
            ],

            [
                'name' => 'Transport géré par le vendeur',
                'code' => 'seller_custom',
                'type' => 'shipping',
                'description' => "Le produit vous est livré par le transporteur choisi par le vendeur.",
                'emc' => false,
            ],
            [
                'name' => 'Chronopost - Chrono 13',
                'code' => 'CHRPChrono13',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h (avant 13h)',
                'emc' => true
            ],

            [
                'name' => 'Chronopost - Chrono 18',
                'code' => 'CHRPChrono18',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h (avant 18h)',
                'emc' => true
            ],

            [
                'name' => 'Chronopost - Chrono Classic',
                'code' => 'CHRPChronoInternationalClassic',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 2 à 4 jours',
                'emc' => true
            ],

            [
                'name' => 'Chronopost - Chrono Relais',
                'code' => 'CHRPChronoRelais',
                'type' => 'parcel_carrier',
                'description' => 'Livraison en relais en 24h',
                'emc' => true
            ],

            [
                'name' => 'Chronopost - Chrono Relais Europe',
                'code' => 'CHRPChronoRelaisEurope',
                'type' => 'parcel_carrier',
                'description' => 'Livraison en relais en 2 à 3 jours',
                'emc' => true
            ],

            [
                'name' => 'DHL Express - Domestic',
                'code' => 'DHLEDomesticExpress',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h',
                'emc' => true
            ],

            [
                'name' => 'DHL Express - Economy Select',
                'code' => 'DHLEEconomySelect',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 2 à 5 jours',
                'emc' => true
            ],

            [
                'name' => 'DHL Express - Worldwide',
                'code' => 'DHLEExpressWorldwide',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h à 72h',
                'emc' => true
            ],

            [
                'name' => 'FedEx - International Economy',
                'code' => 'FEDXInternationalEconomy',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 2 à 6 jours',
                'emc' => true
            ],

            [
                'name' => 'FedEx - International Priority',
                'code' => 'FEDXInternationalPriorityCC',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h à 48h',
                'emc' => true
            ],

            [
                'name' => 'Happy-Post - PackSuiviEurope',
                'code' => 'IMXEPackSuiviEurope',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 3 à 9 jours',
                'emc' => true
            ],

            [
                'name' => 'Mondial Relay - C.pourToi®',
                'code' => 'MONRCpourToi',
                'type' => 'parcel_carrier',
                'description' => 'Livraison en relais en 3 à 5 jours',
                'emc' => true
            ],

            [
                'name' => 'Mondial Relay - C.pourToi® - Europe',
                'code' => 'MONRCpourToiEurope',
                'type' => 'parcel_carrier',
                'description' => 'Livraison en relais en 3 à 6 jours',
                'emc' => true
            ],

            [
                'name' => 'Mondial Relay - Domicile Europe',
                'code' => 'MONRDomicileEurope',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 3 à 6 jours',
                'emc' => true
            ],

            [
                'name' => 'Colissimo - Access France',
                'code' => 'POFRColissimoAccess',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile sans signature en 48h',
                'emc' => true
            ],

            [
                'name' => 'Colissimo - Expert France',
                'code' => 'POFRColissimoExpert',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile contre signature en 48h',
                'emc' => true
            ],
            [
                'name' => 'Sodexi - Inter Express Standard',
                'code' => 'SODXExpressStandardInterColisMarch',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 7 à 10 jours',
                'emc' => true
            ],

            [
                'name' => 'Relais Colis - Relais Colis® Eco',
                'code' => 'SOGPRelaisColis',
                'type' => 'parcel_carrier',
                'description' => 'Livraison en relais en 4 à 6 jours',
                'emc' => true
            ],

            [
                'name' => 'TNT Express - Economy',
                'code' => 'TNTEEconomyExpressInternational',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 2 à 5 jours',
                'emc' => true
            ],

            [
                'name' => 'TNT Express - National 13H',
                'code' => 'TNTEExpressNational',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h (avant 13h)',
                'emc' => true
            ],

            [
                'name' => 'UPS - Express Saver',
                'code' => 'UPSEExpressSaver',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 1 à 5 jours',
                'emc' => true
            ],

            [
                'name' => 'UPS - Standard',
                'code' => 'UPSEStandard',
                'type' => 'parcel_carrier',
                'description' => 'Livraison à domicile en 24h à 72h (avant 19h)',
                'emc' => true
            ],

            [
                'name' => 'UPS - Standard Access Point',
                'code' => 'UPSEStandardAP',
                'type' => 'parcel_carrier',
                'description' => 'Livraison en relais en 24h à 72h (avant 19h)',
                'emc' => true
            ],

        ];

        foreach ($availableDeliveryModes as $statusInfos) {
            $deliveryMode = new DeliveryMode();
            $deliveryMode->setName($statusInfos['name']);
            $deliveryMode->setCode($statusInfos['code']);
            $deliveryMode->setType($statusInfos['type']);
            $deliveryMode->setDescription($statusInfos['description']);
            $deliveryMode->setEmc($statusInfos['emc']);
            $manager->persist($deliveryMode);
        }
        $manager->flush();
    }
}