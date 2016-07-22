<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends Controller
{
    /**
     * @Route("/profil", name="user_account_profile")
     * @Template("UserBundle:Account:profile.html.twig")
     */
    public function profileAction(Request $request)
    {
    }

    /**
     * @Route("/compte/wishlist", name="user_account_wishlist")
     * @Template("UserBundle:Account:wishlist.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function wishlistAction(Request $request)
    {
        $user = $this->getUser();
        $favorites = $user->getFavorites();
        return ['favorites' => $favorites];
    }

    /**
     * @Route("/profil/mes-achats", name="user_account_purchases")
     * @Template("UserBundle:Account:purchases-list.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function purchasesAction()
    {
        $orders = $this->getDoctrine()->getRepository('OrderBundle:Order')->findPurchaseByUser($this->getUser());

        return [
            'orders' => $orders
        ];
    }

    /**
     * @Route("/profil/mes-ventes", name="user_account_sales")
     * @Template("UserBundle:Account:sales-list.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function salesAction()
    {
        $orders = $this->getDoctrine()->getRepository('OrderBundle:Order')->findSaleByUser($this->getUser());

        return [
            'orders' => $orders
        ];
    }
}
