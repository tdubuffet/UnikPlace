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
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Product;
use Symfony\Component\PropertyAccess\PropertyAccess;
use ProductBundle\Entity\ReferentialValue;

class ProductToElasticaTransformer implements ModelToElasticaTransformerInterface
{

    /**
     * Transforme un produit en champs pour elasticsearch
     *
     * @param Product $product
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
            'county' => $product->getAddress()->getCity()->getCounty()->getId()
        ));

        // Import product attributes
        $this->setAttributes($document, $product);

        return $document;
    }

    /**
     * @param Document $document
     * @param Product $product
     */
    private function setAttributes($document, $product)
    {
        $valueTypes = ['text', 'integer', 'float', 'datetime', 'date', 'referential', 'boolean'];
        $accessor = PropertyAccess::createPropertyAccessor();

        $attributesValues = $product->getAttributeValues();
        if (isset($attributesValues)) {
            foreach ($attributesValues as $attributeValue) {
                /** @var Attribute $attribute */
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
}
