<?php

namespace Admin2Bundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="ad2_index")
     */
    public function listAction(Request $request)
    {


        $countOrders            = $this->getDoctrine()->getRepository('OrderBundle:Order')->count();
        $countOrdersAccepted    = $this->getDoctrine()->getRepository('OrderBundle:Order')->count('accepted');
        $countOrdersRefused     = $this->getDoctrine()->getRepository('OrderBundle:Order')->count('refused');
        $countOrdersAwaiting    = $this->getDoctrine()->getRepository('OrderBundle:Order')->count('awaiting');
        $lastOrders             = $this->getDoctrine()->getRepository('OrderBundle:Order')->findBy([], ['createdAt' => 'DESC'], 10);


        $countProducts            = $this->getDoctrine()->getRepository('ProductBundle:Product')->count();
        $countProductsAccepted    = $this->getDoctrine()->getRepository('ProductBundle:Product')->count('published');
        $countProductsAwaiting     = $this->getDoctrine()->getRepository('ProductBundle:Product')->count('awaiting');



        return $this->render('Admin2Bundle:Default:index.html.twig',
        [
            'totalOrders' => $countOrders,
            'totalOrdersAccepted' => $countOrdersAccepted,
            'totalOrdersRefused' => $countOrdersRefused,
            'totalOrdersAwaiting' => $countOrdersAwaiting,
            'orders' => $lastOrders,

            'totalProducts' => $countProducts,
            'totalProductsAccepted' => $countProductsAccepted,
            'totalProductsAwaiting' => $countProductsAwaiting,
        ]);
    }
}
