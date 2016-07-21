<?php

namespace OrderBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OrderBundle\Entity\Status;

class LoadStatusData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableStatus = [
            ['name' => 'pending'],
            ['name' => 'accepted'],
            ['name' => 'canceled'],
            ['name' => 'done'],
            ['name' => 'disputed'],
            ['name' => 'error']];

        foreach ($availableStatus as $statusInfos) {
            $status = new Status();
            $status->setName($statusInfos['name']);
            $manager->persist($status);
        }
        $manager->flush();
    }
}
