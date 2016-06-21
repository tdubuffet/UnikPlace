<?php

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\Status;

class LoadStatusData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableStatus = ['awaiting', 'published', 'deleted', 'expired'];
        foreach ($availableStatus as $statusName) {
            $status = new Status();
            $status->setName($statusName);
            $manager->persist($status);
        }
        $manager->flush();
    }
}