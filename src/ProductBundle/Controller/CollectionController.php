<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 18/07/16
 * Time: 17:56
 */

namespace ProductBundle\Controller;

use ProductBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ProductBundle\Entity\Collection;

/**
 * Class CollectionController
 * @package ProductBundle\Controller
 */
class CollectionController extends Controller
{
    /**
     * @Route("/tendance", name="collections")
     * @Template("ProductBundle:Collection:tendances.html.twig")
     * @return array
     */
    public function collectionsAction()
    {
        $categories = $this->getDoctrine()->getRepository("ProductBundle:Category")->findByWithImageAndCollections();
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findByLast();

        return ["categories" => $categories, 'collections' => $collections];
    }

    /**
     * @Route("/tendance/{slug}", name="collection")
     * @ParamConverter("collection", class="ProductBundle:Collection", options={"slug" = "slug"})
     * @Template("ProductBundle:Collection:index.html.twig")
     * @param Collection $collection
     * @return array
     */
    public function collectionAction(Collection $collection)
    {
        return ['collection' => $collection, 'products' => $collection->getProducts()];
    }

    /**
     * @Route("/tendance-categorie/{slug}", name="collection_categ")
     * @ParamConverter("category", class="ProductBundle:Category", options={"slug" = "slug"})
     * @Template("ProductBundle:Collection:category.html.twig")
     * @param Category $category
     * @return array
     */
    public function collectionCategoriesAction(Category $category)
    {
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findByCategory($category);

        return ['category' => $category, 'collections' => $collections];
    }
}