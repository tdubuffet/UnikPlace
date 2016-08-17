<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 16/08/16
 * Time: 16:37
 */

namespace ProductBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Referential;
use ProductBundle\Entity\ReferentialValue;

/**
 * Class LoadAttributes
 * @package ProductBundle\DataFixtures\ORM
 */
class LoadAttributes extends AbstractFixture implements OrderedFixtureInterface
{
    /** @var array $attributes */
    private $attributes = [
        'style' => [
            'type' => 'string',
            'search' => 'multiselect',
            'deposit' => 'select',
            'mandatory' => false,
            'code' => 'style',
        ],
        'couleur' => [
            'type' => 'string',
            'search' => 'color',
            'deposit' => 'color',
            'mandatory' => false,
            'code' => 'color',
        ],
        'matériau' => [
            'type' => 'string',
            'search' => 'multiselect',
            'deposit' => 'select',
            'mandatory' => true,
            'code' => 'material',
        ],
        'designer' => [
            'type' => 'string',
            'search' => 'select2',
            'deposit' => 'select2',
            'mandatory' => false,
            'code' => 'designer',
        ],
        'marque' => [
            'type' => 'string',
            'search' => 'select2',
            'deposit' => 'select2',
            'mandatory' => true,
            'code' => 'brand',
        ],
        'État' => [
            'type' => 'string',
            'search' => 'multiselect',
            'deposit' => 'select',
            'mandatory' => false,
            'code' => 'condition',
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $referentiels = include(__DIR__."/referentiels.php");

        foreach ($referentiels as $referentiel => $values) {
            $infos = $this->attributes[$referentiel];
            $ref = new Referential();
            $ref->setName(ucfirst($referentiel))->setCode($infos['code']);
            $manager->persist($ref);
            $manager->flush();
            $this->loadAttribute($manager, $ref, $infos);

            foreach ($values as $value) {
                $obj = new ReferentialValue();
                $obj->setValue($value)->addReferential($ref);
                $manager->persist($obj);
            }
            $manager->flush();
        }
    }

    /**
     * @param ObjectManager $manager
     * @param Referential $referential
     * @param array $infos
     * @return Attribute
     */
    public function loadAttribute(ObjectManager $manager, Referential $referential, array $infos)
    {
        $attributeObj = $manager->getRepository('ProductBundle:Attribute')
            ->findOneBy(['code' => $referential->getCode()]);
        if ($attributeObj) {
            return $attributeObj;
        }
        $deposit = $manager->getRepository('ProductBundle:AttributeDepositTemplate')
            ->findOneBy(['name' => $infos['deposit']]);
        $search = $manager->getRepository('ProductBundle:AttributeSearchTemplate')
            ->findOneBy(['name' => $infos['search']]);
        $type = $manager->getRepository('ProductBundle:AttributeType')
            ->findOneBy(['name' => $infos['type']]);
        $attribute = new Attribute();
        $attribute
            ->setCode($referential->getCode())
            ->setName(ucfirst($referential->getName()))
            ->setReferential($referential)
            ->setAttributeDepositTemplate($deposit)
            ->setAttributeSearchTemplate($search)
            ->setAttributeType($type)
            ->setMandatory($infos['mandatory']);

        $manager->persist($attribute);
        $manager->flush();

        return $attribute;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 5;
    }
}