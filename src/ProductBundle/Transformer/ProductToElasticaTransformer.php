<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 30/07/15
 * Time: 16:51
 */

namespace ProductBundle\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use ProductBundle\Entity\ReferentialValue;

class ProductToElasticaTransformer implements ModelToElasticaTransformerInterface
{

    /**
     * Transforme un produit en champs pour elasticsearch
     *
     * @param object $product
     * @param array $fields
     * @return Document
     */
    public function transform($product, array $fields)
    {
        $identifier = $product->getId();

        $document = new Document($identifier, array(
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'category' => $product->getCategory()->getPath(),
            'price' => $product->getPrice(),
            'updated_at' => $product->getUpdatedAt()->getTimestamp(),
        ));

        // Import product attributes
        $this->setAttributes($document, $product);

        return $document;
    }

    private function setAttributes($document, $product)
    {
        $valueTypes = ['text', 'integer', 'float', 'datetime', 'date', 'referential', 'boolean'];
        $accessor = PropertyAccess::createPropertyAccessor();

        $attributesValues = $product->getAttributeValues();
        foreach ($attributesValues as $attributeValue) {
            $attribute = $attributeValue->getAttribute();
            foreach ($valueTypes as $valueType) {
                $value = $accessor->getValue($attributeValue, $valueType.'_value');
                if (!is_null($value)) {
                    if ($value instanceof ReferentialValue) {
                        $value = $value->getId();
                    }
                    // Cast to the right type
                    $type = $attribute->getAttributeType()->getName();
                    if (isset($type) && in_array($type, ['string', 'boolean', 'integer', 'float'])) {
                        settype($value, $type);
                    }
                    if (!$document->has($attribute->getCode())) {
                        $document->set($attribute->getCode(), $value);
                    }
                }
            }
        }
    }

}
