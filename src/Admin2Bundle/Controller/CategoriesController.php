<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Form\CreateCategoryForm;
use ProductBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/ad2/category")
 */
class CategoriesController extends Controller
{
    /**
     * @Route("/", name="ad2_categories_list")
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
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
        $twigArray = [];
        $form = $this->createForm(CreateCategoryForm::class, null);
        $form->handleRequest($request);
        $twigArray['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isSubmitted()) {
            /** @var Category $category */
            $category = $form->getData();
            $this->getDoctrine()->getManager()->persist($category);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('Catégorie %s créée', $category->getName()));

            return $this->redirectToRoute("ad2_categories_list");
        }

        return $this->render('Admin2Bundle:Categories:create.html.twig', $twigArray);
    }
}
