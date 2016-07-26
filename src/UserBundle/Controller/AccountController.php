<?php

namespace UserBundle\Controller;

use OrderBundle\Entity\Order;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use ProductBundle\Listener\OrderListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /**
     * @Route("/portefeuille/transfert", name="user_account_wallet_tranfer")
     */
    public function transfertBankAction(Request $request)
    {

        $user = $this->getUser();

        if ( count($this->get('mangopay_service')->getIbanBank($user->getMangopayUserId())) == 0) {
            return $this->redirectToRoute('user_account_bank');
        }

        if ($this->get('mangopay_service')->validateWalletToTransferBank($user->getFreeWallet()->Id) == false) {
            return $this->redirectToRoute('user_account_wallet', [ 'transfer' => 'failed_payout_exist' ]);
        }

        $this->get('mangopay_service')->freeWalletToTransferBank($user->getFreeWallet()->Id);

        return $this->redirectToRoute('user_account_wallet', [ 'transfer' => 'ok' ]);
    }


    /**
     * @Route("/portefeuille/rib", name="user_account_bank")
     * @Template("UserBundle:Account:bank.html.twig")
     */
    public function accountBankAction(Request $request)
    {

        $data       = [];
        $user       = $this->getUser();
        $ibanBank   = $this->get('mangopay_service')->getIbanBank(
            $user->getMangopayUserId()
        );

        if ($ibanBank) {

            $data = [
                'iban' => $ibanBank->Details->IBAN,
                'bic'  => $ibanBank->Details->BIC,
                'name'  => $ibanBank->OwnerName,
                'address_street'  => $ibanBank->OwnerAddress->AddressLine1,
                'address_postal_code'  => $ibanBank->OwnerAddress->PostalCode,
                'address_city'  => $ibanBank->OwnerAddress->City,
                'address_country'  => $ibanBank->OwnerAddress->Country
            ];
        }

        $form = $this->createFormBuilder($data)
            ->add('iban', TextType::class, [
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Iban([
                        'message' => 'Le numéro de compte banquaire international n\'est pas valide.'
                    ])
                ],
                'label' => 'IBAN'
            ])
            ->add('bic', TextType::class, [
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Bic([
                        'message' => 'Le numéro BIC n\'est pas valide.'
                    ])
                ],
                'label' => 'BIC'
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom et Prénom'
            ])
            ->add('address_street', TextType::class, [
                'label' => 'Adresse'
            ])
            ->add('address_postal_code', TextType::class, [
                'label' => 'Code postal'
            ])
            ->add('address_city', TextType::class, [
                'label' => 'Ville'
            ])
            ->add('address_country', CountryType::class, [
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Country([
                        'message' => 'Le pays de résidence n\'est pas valide.'
                    ])
                ],
                'label' => 'Pays de résidence'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('mangopay_service')->createIbanBank($user->getMangopayUserId(), $form->getData());
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/achat/{id}", name="user_account_purchase")
     * @Route("/vente/{id}", name="user_account_sale")
     * @Template("UserBundle:Account:order.html.twig")
     */
    public function orderAction(Request $request, Order $order)
    {

        $sale = false;

        $routeName = $request->get('_route');

        if ($routeName == 'user_account_sale') {
            $sale = true;
        }

        if ($routeName == 'user_account_purchase' && $order->getUser() != $this->getUser()) {
            throw new NotFoundHttpException('Not found Order');
        }

        if ($routeName == 'user_account_sale' && $order->getProduct()->getUser() != $this->getUser()) {
            throw new NotFoundHttpException('Not found Order');
        }

        $this->get('order_listener')->listen($request, $order);

        return [
            'order' => $order,
            'sale' => $sale
        ];

    }


}
