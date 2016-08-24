<?php

namespace ProductBundle\Service;

use Doctrine\ORM\EntityManager;
use ProductBundle\Entity\Category;

class CategoryService
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
     * Get tree of subcategories
     *
     * @param Category $category
     *
     * @return array
     */
    public function getSubCategories($category)
    {
        $getSubCategoriesTree = function($category, $result = []) use (&$getSubCategoriesTree) {
            $subCategories = $this->em->getRepository('ProductBundle:Category')->findByParentCache($category);

            if ($subCategories) {
                $subCategInfos = [];
                /** @var Category $subCateg */
                foreach ($subCategories as $subCateg) {
                    $subCategInfos[$subCateg->getId()] = ['id' => $subCateg->getId(), 'name' => $subCateg->getName()];

                    $children = $getSubCategoriesTree($subCateg, $result);
                    if (count($children) > 0) {
                        $subCategInfos[$subCateg->getId()]['children'] = $children;
                    }
                }
                $result = $subCategInfos;
            }
            return $result;
        };

        return $getSubCategoriesTree($category);
    }

}
