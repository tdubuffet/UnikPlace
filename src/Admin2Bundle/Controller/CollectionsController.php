<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 25/08/16
 * Time: 16:53
 */

namespace Admin2Bundle\Controller;

use Admin2Bundle\Form\CollectionForm;
use ProductBundle\Entity\Category;
use ProductBundle\Entity\Collection;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CollectionsController
 * @package Admin2Bundle\Controller
 * @Route("/ad2/collection")
 */
class CollectionsController extends Controller
{
    /**
     * @Route("/", name="ad2_collections_list")
     * @return Response
     */
    public function listAction()
    {
        $collections = $this->getDoctrine()->getRepository('ProductBundle:Collection')->findAll();

        return $this->render('Admin2Bundle:Collections:list.html.twig', ['collections' => $collections]);
    }

    /**
     * @Route("/new", name="ad2_collections_new")
     * @param Request $request
     * @return Response
     */
    public function createCollectionAction(Request $request)
    {
        $form = $this->createForm(CollectionForm::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $products = !$request->request->get('products') ? [] : $request->request->get('products');
            $collection = $this->persistManytoMany($form->getData(), $products);
            $this->getDoctrine()->getManager()->persist($collection);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", sprintf('Tendance %s créée', $collection->getName()));

            return $this->redirectToRoute("ad2_collections_list");
        }

        return $this->render('Admin2Bundle:Collections:create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_collections_edit")
     * @ParamConverter("collection", class="ProductBundle:Collection")
     * @param Request $request
     * @param Collection $collection
     * @return Response
     */
    public function editCollectionAction(Request $request, Collection $collection)
    {
        $twigArray['collection'] = $collection;
        $form = $this->createForm(CollectionForm::class, $collection, ['img_req' => false]);
        $form->handleRequest($request);
        $twigArray['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->get('collection_form');
            $dataImg = isset($data['image'], $data['image']['imageFile'], $data['image']['imageFile']['delete']);
            if ($dataImg && $data['image']['imageFile']['delete']) {
                $collection->getImage()->setCollection(null);
                $this->getDoctrine()->getManager()->persist($collection->getImage());
                $collection->setImage(null);
            }
            $products = !$request->request->get('products') ? [] : $request->request->get('products');
            $collection = $this->persistManytoMany($collection, $products);
            $this->getDoctrine()->getManager()->persist($collection);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", sprintf('Tendance %s modifiée', $collection->getName()));

            return $this->redirectToRoute("ad2_collections_list");
        }

        return $this->render('Admin2Bundle:Collections:create.html.twig', $twigArray);
    }

    /**
     * @Route("/ajax-products", name="ad2_collections_products", options={"expose": "true"})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function findProductsAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['message' => 'You must be authentificated to remove a category.'], 401);
        }

        if (!$request->request->has('collection_form')) {
            return new JsonResponse(['message' => 'Parameter collection_form missing.'], 401);
        }

        $final = [];
        $col_form = $request->request->get('collection_form');
        $ids = !isset($col_form['categories']) ? [] : $col_form['categories'];
        $productsList = !$request->request->get('products') ? [] : $request->request->get('products');
        foreach ($ids as $id) {
            $category = $this->getDoctrine()->getRepository("ProductBundle:Category")->findOneBy(['id' => $id]);
            if (!$category) {
                continue;
            }
            /** @var Product $product */
            foreach ($category->getProducts() as $product) {
                $final[$product->getId()] = ['value' => $product->getId(), 'text' => $product->__toString()];
            }
        }
        foreach ($productsList as $key => $item) {
            if (!array_key_exists($item, $final)) {
                unset($productsList[$key]);
            }
        }

        return new JsonResponse(['products' => $final, 'list' => $productsList]);
    }

    /**
     * @Route("/remove", name="ad2_collection_remove", options={"expose": "true"})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCollectionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['message' => 'You must be authentificated to remove a collection.'], 401);
        }

        if (!$request->request->has('collection_id')) {
            return new JsonResponse(['message' => 'Parameter collection_id missing.'], 401);

        }
        $id = $request->request->get('collection_id');
        $collection = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findOneBy(['id' => $id]);
        if (!$collection) {
            return new JsonResponse(['message' => 'Collection not found'], 404);
        }

        $this->getDoctrine()->getManager()->remove($collection->getImage());
        $this->getDoctrine()->getManager()->remove($collection);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash("success", sprintf('Tendance %s supprimée', $collection->getName()));

        return new JsonResponse(['message' => sprintf('Tendance %s supprimée', $collection->getName())]);
    }

    /**
     * @param Collection $collection
     * @param array $products
     * @return Collection
     */
    private function persistManytoMany(Collection $collection, $products)
    {
        foreach ($products as $id) {
            $product = $this->getDoctrine()->getRepository("ProductBundle:Product")->findOneBy(['id' => $id]);
            if ($product) {
                $collection->addProduct($product);
            }
        }

        /** @var Product $product */
        foreach ($collection->getProducts() as $product) {
            if (!$product->getCollections()->contains($collection)) {
                $product->addCollection($collection);
                $this->getDoctrine()->getManager()->persist($product);
            }
        }
        /** @var Category $category */
        foreach ($collection->getCategories() as $category) {
            if (!$category->getCollections()->contains($collection)) {
                $category->addCollection($collection);
                $this->getDoctrine()->getManager()->persist($category);
            }
        }
        if ($collection->getImage()) {
            $collection->getImage()->setCollection($collection);
            $this->getDoctrine()->getManager()->persist($collection->getImage());
        }

        return $collection;
    }

}