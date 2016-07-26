<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\Status;

class LoadStatusData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableStatus = [
            ['name' => 'awaiting',    'label' => 'En modération'],
            ['name' => 'published',   'label' => 'Publié'],
            ['name' => 'deleted',     'label' => 'Supprimé'],
            ['name' => 'expired',     'label' => 'Expiré'],
            ['name' => 'sold',        'label' => 'Vendu'],
            ['name' => 'unavailable', 'label' => 'Non disponible à la vente'],
            ['name' => 'refused'    , 'label' => 'Refusé']];

        foreach ($availableStatus as $statusInfos) {
            $status = new Status();
            $status->setName($statusInfos['name']);
            $status->setLabel($statusInfos['label']);
            $manager->persist($status);
        }
        $manager->flush();
    }
}