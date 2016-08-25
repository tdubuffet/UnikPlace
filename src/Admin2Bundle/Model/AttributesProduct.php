<?php
namespace Admin2Bundle\Model;

use ProductBundle\Entity\Product;

/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 25/08/16
 * Time: 12:49
 */
class AttributesProduct
{

    private $twig;

    public function __construct($twig)
    {

        $this->twig = $twig;

    }


    public function getAttributes(Product $product, &$filters = array())
    {
        $attributes = $product->getCategory()->getAttributes();

        $values = $product->getAttributeValues();

        foreach ($attributes as $attribute) {
            $template = $attribute->getAttributeDepositTemplate();
            $filters[$attribute->getCode()] = [
                'template' => $template->getName(),
                'viewVars' => [
                    'label'     => $attribute->getName(),
                    'id'        => $attribute->getCode(),
                    'mandatory' => $attribute->getMandatory(),
                ],
            ];

            foreach($values as $val) {
                if ($val->getAttribute() == $attribute) {
                    $filters[$attribute->getCode()]['viewVars']['selected'] = $val->getReferentialValue();
                }
            }
            $referential = $attribute->getReferential();
            if (isset($referential)) {
                $filters[$attribute->getCode()]['viewVars']['referentialName'] = $referential->getName();
                $filters[$attribute->getCode()]['viewVars']['referentialValues'] = $referential->getReferentialValues();
            }
        }

        $customFields = [];
        if (count($filters) > 0) {
            foreach ($filters as $filter) {
                $customFields[] = $this->twig->render(
                    'DepositBundle:DepositFilters:'.$filter['template'].'.html.twig',
                    isset($filter['viewVars']) ? $filter['viewVars'] : []
                );
            }
        }

        return $customFields;
    }

}