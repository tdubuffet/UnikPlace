<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\AttributeValue;
use Symfony\Component\PropertyAccess\PropertyAccess;
use ProductBundle\Entity\ReferentialValue;

class ProductAttributeService
{
    /**
     *
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * Get product attributes directly
     *
     * @param \ProductBundle\Entity\Product $product
     *
     * @return array
     */
    public function getAttributesFromProduct($product)
    {
        $attributes = array();
        /** @var AttributeValue $value */
        foreach ($product->getAttributeValues() as $value) {
            $attribute = $value->getAttribute();
            $attributes[$attribute->getCode()] = [
                'name' => $attribute->getName(),
                'value' => $this->getAttributebyCodeFromProduct($attribute->getCode(), $product),
                'deposit' => $attribute->getAttributeDepositTemplate()->getName(),
            ];
        }
        return $attributes;
    }

    /*
     * Get product attribute by code
     *
     * @param string $code
     * @param \ProductBundle\Entity\Product $product
     *
     * @return mixed
     */
    public function getAttributebyCodeFromProduct($code, $product)
    {
        // Find attribute types
        $objectTypes = $this->em->getRepository('ProductBundle:AttributeType')->findAll();
        $types = array();
        foreach ($objectTypes as $type) {
            $types[$type->getId()] = $type->getName();
        }
        $attribute = $this->em->getRepository('ProductBundle:Attribute')->findOneByCode($code);
        if (!isset($attribute)) {
            return null;
        }
        $attributeValue = $this->em->getRepository('ProductBundle:AttributeValue')->findOneBy(
            ['attribute' => $attribute, 'product' => $product]);
        if (!isset($attributeValue)){
            return null;
        }
        // Find value
        $valueTypes = ['text', 'integer', 'float', 'datetime', 'date', 'referential', 'boolean'];
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($valueTypes as $valueType) {
            $value = $accessor->getValue($attributeValue, $valueType.'_value');
            if (!is_null($value)) {
                if ($value instanceof ReferentialValue) {
                    $value = $value->getValue();
                }
                // Cast to the right type
                $type = $attribute->getAttributeType()->getName();
                if (isset($type) && in_array($type, ['string', 'boolean', 'integer', 'float'])) {
                    settype($value, $type);
                }
                return $value;
            }
        }
        return null;
    }


}
