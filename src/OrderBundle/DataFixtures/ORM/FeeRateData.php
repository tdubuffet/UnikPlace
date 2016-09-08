<?php

namespace OrderBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OrderBundle\Entity\FeeRate;

class FeeRateData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $availableFeeRates = [
            // Individual
            [
                'rate' => 15,
                'minimum' => 0,
                'type' => 'individual',
            ],
            [
                'rate' => 12,
                'minimum' => 500,
                'type' => 'individual',
            ],
            [
                'rate' => 10,
                'minimum' => 1000,
                'type' => 'individual',
            ],
            [
                'rate' => 8,
                'minimum' => 2000,
                'type' => 'individual',
            ],

            // Pro
            [
                'rate' => 15,
                'minimum' => 0,
                'type' => 'pro',
            ],
            [
                'rate' => 12,
                'minimum' => 500,
                'type' => 'pro',
            ],
            [
                'rate' => 10,
                'minimum' => 1000,
                'type' => 'pro',
            ],
            [
                'rate' => 8,
                'minimum' => 2000,
                'type' => 'pro',
            ],
        ];

        foreach ($availableFeeRates as $feeRateInfo) {
            $feeRate = new FeeRate();
            $feeRate->setRate($feeRateInfo['rate']);
            $feeRate->setMinimum($feeRateInfo['minimum']);
            $feeRate->setType($feeRateInfo['type']);
            $manager->persist($feeRate);
        }
        $manager->flush();
    }
}