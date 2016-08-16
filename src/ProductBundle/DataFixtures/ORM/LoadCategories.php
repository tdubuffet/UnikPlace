<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 16/08/16
 * Time: 12:00
 */

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\Category;

class LoadCategories implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {

    }

}