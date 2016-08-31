<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 30/08/16
 * Time: 10:48
 */

namespace Admin2Bundle\Controller;

use Admin2Bundle\Form\BlogCategoryType;
use BlogBundle\Entity\BlogCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/blog_category")
 */
class BlogCategoryController extends Controller
{
    /**
     * @Route("/", name="ad2_blog_categories_list")
     * @return Response
     */
    public function listAction()
    {
        $categories = $this->getDoctrine()->getRepository('BlogBundle:BlogCategory')->findAll();

        return $this->render('Admin2Bundle:BlogCategories:list.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/new", name="ad2_blog_categories_new")
     * @param Request $request
     * @return Response
     */
    public function createCategoryAction(Request $request)
    {
        $form = $this->createForm(BlogCategoryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BlogCategory $category */
            $category = $form->getData();
            $this->getDoctrine()->getManager()->persist($category);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", sprintf('Catégorie %s créée', $category->getName()));

            return $this->redirectToRoute("ad2_blog_categories_list");
        }

        return $this->render('Admin2Bundle:BlogCategories:create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_blog_categories_edit")
     * @ParamConverter("category", class="BlogBundle:BlogCategory")
     * @param Request $request
     * @param BlogCategory $category
     * @return Response
     */
    public function editCategoryAction(Request $request, BlogCategory $category)
    {
        $twigArray['category'] = $category;
        $form = $this->createForm(BlogCategoryType::class, $category);
        $form->handleRequest($request);
        $twigArray['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BlogCategory $category */
            $category = $form->getData();
            $this->getDoctrine()->getManager()->persist($category);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash("success", sprintf('Catégorie %s modifiée', $category->getName()));

            return $this->redirectToRoute("ad2_blog_categories_list");

        }

        return $this->render('Admin2Bundle:BlogCategories:create.html.twig', $twigArray);
    }

}