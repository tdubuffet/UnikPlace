<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 18/07/16
 * Time: 17:56
 */

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ProductBundle\Entity\Collection;

/**
 * Class CollectionController
 * @package ProductBundle\Controller
 */
class CollectionController extends Controller
{
    /**
     * @Route("/col/{slug}", name="collection")
     * @ParamConverter("collection", class="ProductBundle:Collection", options={"slug" = "slug"})
     * @Template("ProductBundle:Collection:index.html.twig")
     * @param Collection $collection
     * @return array
     */
    public function collectionAction(Collection $collection)
    {
        return ['collection' => $collection, 'products' => $collection->getProducts()];
    }

}