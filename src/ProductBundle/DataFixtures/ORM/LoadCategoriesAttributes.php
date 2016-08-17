<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 16/08/16
 * Time: 16:45
 */

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadCategoriesAttributes
 * @package ProductBundle\DataFixtures\ORM
 */
class LoadCategoriesAttributes extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $categories = LoadCategories::$categories;
        foreach ($categories as $main => $subs) {
            foreach ($subs as $sub => $attributes) {
                $sub = ucfirst($sub);
                $subCat = $manager->getRepository("ProductBundle:Category")->findOneBy(['name' => $sub]);
                foreach ($attributes as $attribute) {
                    $attributeObj = $manager->getRepository("ProductBundle:Attribute")
                        ->findOneBy(['code' => $attribute]);
                    if (!$attributeObj) {
                        throw new \Exception(sprintf("Attribute with code '%s' not found", $attribute));
                    }
                    $attributeObj->addCategory($subCat);
                    $manager->persist($attributeObj);
                    $manager->flush();
                }
            }
        }
    }

    /**
     * @return integer
     */
    public function getOrder()
    {
        return 6;
    }

}