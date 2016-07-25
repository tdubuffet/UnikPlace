<?php

namespace UserBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Form\PreferenceFormType;

/**
 * Class AccountController
 * @package UserBundle\Controller
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/compte")
 */
class AccountController extends Controller
{
    /**
     * @Route("", name="user_account_profile")
     * @Template("UserBundle:Account:profile.html.twig")
     */
    public function profileAction(Request $request)
    {
        /*
         * @todo A supprimer
         */
    }

    /**
     * @Route("/wishlist", name="user_account_wishlist")
     * @Template("UserBundle:Account:wishlist.html.twig")
     */
    public function wishlistAction(Request $request)
    {

        return [
            'favorites' => $this->getUser()->getFavorites()
        ];
    }

    /**
     * @Route("/preferences", name="user_account_preference")
     * @Template("UserBundle:Account:preference.html.twig")
     */
    public function preferenceAction(Request $request)
    {

        $user = $this->getUser();

        $form = $this->createForm(PreferenceFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/achats", name="user_account_purchases")
     * @Template("UserBundle:Account:purchases-list.html.twig")
     */
    public function purchasesAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('OrderBundle:Order')->findPurchaseByUser($this->getUser());


        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'orders' => $pagerfanta
        ];
    }

    /**
     * @Route("/ventes", name="user_account_sales")
     * @Template("UserBundle:Account:sales-list.html.twig")
     */
    public function salesAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('OrderBundle:Order')->findSaleByUser($this->getUser());

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'orders' => $pagerfanta
        ];
    }

    /**
     * @Route("/portefeuille", name="user_account_wallet")
     * @Template("UserBundle:Account:wallet.html.twig")
     */
    public function walletAction(Request $request)
    {


        $currentPage    = $request->get('page', 1);
        $pagination     = new \MangoPay\Pagination($currentPage, 10);
        $transactions   = $this->get('mangopay_service')->getFreeWalletTransactions($this->getUser()->getMangopayFreeWalletId(), $pagination);

        return [
            'transactions' => $transactions,
            'wallet' => $this->get('mangopay_service')->getWalletId($this->getUser()->getMangopayFreeWalletId())
        ];
    }
}
