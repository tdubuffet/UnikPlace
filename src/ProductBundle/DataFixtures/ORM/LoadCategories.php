<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 16/08/16
 * Time: 12:00
 */

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\Category;

/**
 * Class LoadCategories
 * @package ProductBundle\DataFixtures\ORM
 */
class LoadCategories extends AbstractFixture implements OrderedFixtureInterface
{
    /** @var array $categories */
    public static $categories = [
        'S\'asseoir' => [
            'Canapé' => ['brand', 'designer', 'material', 'color', 'condition', 'style'],
            'Fauteuil' => ['brand', 'designer', 'material', 'color', 'condition', 'style'],
            'Tabouret' => ['brand', 'designer', 'material', 'color', 'condition', 'style'],
            'Chaise' => ['brand', 'designer', 'material', 'color', 'condition', 'style'],
        ],
        'Meubler' => [
            'Table' => ['brand', 'designer', 'material', 'color', 'condition', 'style'],
            'Table basse' => ['brand', 'designer', 'material', 'color', 'condition', 'style'],
            'lit' => ['material', 'brand', 'color', 'condition', 'style'],
            'console' => ['material', 'brand', 'designer', 'color', 'style'],
            'bureau' => ['material', 'brand', 'designer', 'color', 'style'],
            'Armoire' => ['brand', 'material', 'color', 'condition', 'style'],
            'Commode' => ['brand', 'material', 'color', 'condition', 'style'],
            'Buffet' => ['brand', 'material', 'color', 'condition', 'style'],
            'Coffre' => ['brand', 'material', 'color', 'condition', 'style'],
            'Bibliothèque' => ['brand', 'material', 'color', 'condition', 'style'],
            'Poêle à bois' => ['brand', 'material', 'color', 'condition', 'style'],
        ],
        'Décorer' => [
            'Art de la table' => ['brand', 'designer', 'material', 'color', 'style'],
            'Miroir' => ['brand', 'designer', 'material', 'color', 'style'],
            'Décoration murale' => ['brand', 'designer', 'material', 'color', 'style'],
            'Tapis' => ['brand', 'material', 'color', 'condition', 'style'],
        ],
        'Eclairer' => [
            'Lampe' => ['brand', 'designer', 'material', 'color', 'style'],
            'Suspension' => ['brand', 'designer', 'material', 'color', 'style'],
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadCategories($manager);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function loadCategories(ObjectManager $manager)
    {
        //Main category
        foreach (self::$categories as $main => $subs) {
            $main = ucfirst($main);
            $mainCat = $manager->getRepository("ProductBundle:Category")->findOneBy(['name' => $main]);
            if (!$mainCat) {
                $mainCat = new Category();
                $mainCat->setName($main);
                $manager->persist($mainCat);
                $manager->flush();
            }

            //Sub category
            foreach ($subs as $sub => $attributes) {
                $sub = ucfirst($sub);
                $subCat = $manager->getRepository("ProductBundle:Category")->findOneBy(['name' => $sub]);
                if (!$subCat) {
                    $subCat = new Category();
                    $subCat->setName($sub)->setParent($mainCat);
                    $manager->persist($subCat);
                    $manager->flush();
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 4;
    }

}