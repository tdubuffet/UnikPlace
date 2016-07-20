<?php

namespace LocationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LocationBundle\Entity\City;
use LocationBundle\Entity\County;
use Symfony\Component\Yaml\Yaml;

class LoadLocationData implements FixtureInterface {

    public function load(ObjectManager $manager)
    {
        $counties = include(__DIR__."/county.php");
        foreach ($counties as $county) {
            $obj = new County();
            $obj->setName($county['name'])->setCode($county['code']);
            $manager->persist($obj);
        }

        $manager->flush();

        $cities = include(__DIR__."/city.php");
        foreach ($cities as $city) {
            $obj = new City();
            $county = $manager->getRepository("LocationBundle:County")->findOneBy(['id' => $city['county_id']]);
            $obj->setName($city['name'])->setZipcode($city['zipcode'])->setCounty($county);
            $manager->persist($obj);
        }

        $manager->flush();
    }

}