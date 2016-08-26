<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Form\CreateCategoryForm;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Category;
use ProductBundle\Entity\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/category")
 */
class CategoriesController extends Controller
{
    /**
     * @Route("/", name="ad2_categories_list")
     * @return Response
     */
    public function listAction()
    {
        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findBy(['parent' => null]);

        return $this->render('Admin2Bundle:Categories:list.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/new", name="ad2_categories_new")
     * @param Request $request
     * @return Response
     */
    public function createCategoryAction(Request $request)
    {
        if ($request->query->has('parent')) {
            $id = $request->query->get('parent');
            $parent = $this->getDoctrine()->getRepository('ProductBundle:Category')->findOneBy(['id' => $id]);
            $category = new Category();
            $category->setParent($parent);
            $form = $this->createForm(CreateCategoryForm::class, $category, ['img_req' => false]);
        } else {
            $form = $this->createForm(CreateCategoryForm::class, null, ['img_req' => false]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $this->persistManytoMany($form->getData());

            $this->getDoctrine()->getManager()->persist($category);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", sprintf('Catégorie %s créée', $category->getName()));

            return $this->redirectToRoute("ad2_categories_list");
        }

        return $this->render('Admin2Bundle:Categories:create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_categories_edit")
     * @ParamConverter("category", class="ProductBundle:Category")
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function editCategoryAction(Request $request, Category $category)
    {
        $twigArray['category'] = $category;
        $form = $this->createForm(CreateCategoryForm::class, $category, ['img_req' => false]);
        $form->handleRequest($request);
        $twigArray['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $this->persistManytoMany($form->getData());
            $data = $request->request->get('create_category_form');
            $dataImg = isset($data['image'], $data['image']['imageFile'], $data['image']['imageFile']['delete']);
            if ($dataImg && $data['image']['imageFile']['delete']) {
                $category->setImage(null);
            }

            $this->getDoctrine()->getManager()->persist($category);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", sprintf('Catégorie %s modifiée', $category->getName()));

            return $this->redirectToRoute("ad2_categories_edit", ['id' => $category->getId()]);

        }

        return $this->render('Admin2Bundle:Categories:edit.html.twig', $twigArray);
    }

    /**
     * @param Category $category
     * @return Category
     */
    private function persistManytoMany(Category $category)
    {
        /** @var Attribute $attr */
        foreach ($category->getAttributes() as $attr) {
            if (!$attr->getCategories()->contains($category)) {
                $attr->addCategory($category);
                $this->getDoctrine()->getManager()->persist($attr);
            }
        }
        /** @var Category $child */
        foreach ($category->getChildren() as $child) {
            $child->setParent($category);
            $this->getDoctrine()->getManager()->persist($child);
        }
        /** @var Collection $collection */
        foreach ($category->getCollections() as $collection) {
            if (!$collection->getCategories()->contains($category)) {
                $collection->addCategory($category);
                $this->getDoctrine()->getManager()->persist($collection);
            }
        }

        return $category;
    }
}
