<?php

namespace LocationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LocationBundle\Entity\Location;
use Symfony\Component\Yaml\Yaml;

class LoadLocationData implements FixtureInterface {

    public function load(ObjectManager $manager)
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'location.yml';

        $yml = Yaml::parse(file_get_contents($filename));
        foreach ($yml as $data) {
            $location = new Location();

            $location->setCity($data['ville_nom_reel']);
            $location->setZipcode($data['ville_code_postal']);
            $location->setCounty($data['ville_departement']);

            $manager->persist($location);
        }

        $manager->flush();
    }

}