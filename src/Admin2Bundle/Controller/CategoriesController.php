<?php

namespace Admin2Bundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/ad2/category")
 */
class CategoriesController extends Controller
{
    /**
     * @Route("/", name="ad2_categories_list")
     */
    public function listAction(Request $request)
    {


        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')->findBy(['parent' => null]);


        return $this->render('Admin2Bundle:Categories:list.html.twig', [
            'categories' => $categories
        ]);
    }
}
