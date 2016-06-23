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
            'category' => $product->getCategory()->getPath()
        ));

        return $document;

    }
}
