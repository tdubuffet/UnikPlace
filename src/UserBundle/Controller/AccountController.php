<?php

namespace UserBundle\Controller;

use Doctrine\Common\Util\Debug;
use LocationBundle\Entity\Address;
use LocationBundle\Form\AddressType;
use OrderBundle\Entity\Order;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UserBundle\Entity\User;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\AttributeValue;
use OrderBundle\Entity\Delivery;
use UserBundle\Form\PreferenceFormType;
use UserBundle\Form\RatingType;
use UserBundle\Form\MangopayKYCNaturalType;
use UserBundle\Form\MangopayKYCLegalType;
use ProductBundle\Form\ProductType;
use Admin2Bundle\Model\AttributesProduct;

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
     * @Route("/wishlist", name="user_account_wishlist")
     * @Template("UserBundle:Account:wishlist.html.twig")
     * @param Request $request
     * @return array
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
     * @param Request $request
     * @return array
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
     * @param Request $request
     * @return array
     */
    public function purchasesAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('OrderBundle:Order')->findPurchaseByUser($this->getUser());


        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'orders' => $pagerfanta
        ];
    }

    /**
     * @Route("/ventes", name="user_account_sales")
     * @Template("UserBundle:Account:sales-list.html.twig")
     * @param Request $request
     * @return array
     */
    public function salesAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository('OrderBundle:Order')->findSaleByUser($this->getUser());

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return [
            'orders' => $pagerfanta
        ];
    }

    /**
     * @Route("/portefeuille", name="user_account_wallet")
     * @Template("UserBundle:Account:wallet.html.twig")
     * @param Request $request
     * @return array
     */
    public function walletAction(Request $request)
    {


        $currentPage = $request->get('page', 1);
        $pagination = new \MangoPay\Pagination($currentPage, 10);
        $transactions = $this->get('mangopay_service')->getFreeWalletTransactions(
            $this->getUser()->getMangopayFreeWalletId(),
            $pagination
        );

        $transferts = $this->getDoctrine()->getRepository('OrderBundle:TransactionPayTransfert')->findAllTransactionsByUser($this->getUser());

        return [
            'transactions' => $transactions,
            'transferts' => $transferts,
            'wallet' => $this->get('mangopay_service')->getWalletId($this->getUser()->getMangopayFreeWalletId())
        ];
    }

    /**
     * @Route("/portefeuille/transfert", name="user_account_wallet_tranfer")
     * @param Request $request
     * @return array
     */
    public function transfertBankAction(Request $request)
    {

        $user = $this->getUser();

        if (count($this->get('mangopay_service')->getIbanBank($user->getMangopayUserId())) == 0) {
            return $this->redirectToRoute('user_account_bank');
        }

        if ($this->get('mangopay_service')->validateWalletToTransferBank($user->getMangopayFreeWalletId()) == false) {
            return $this->redirectToRoute('user_account_wallet', ['transfer' => 'failed_payout_exist']);
        }

        // Check kyc for seller
        if (!$this->get('mangopay_service')->isKYCValidUser($this->getUser())) {
            $this->get('session')->getFlashBag()->add('kyc_errors',
              "Vous avez atteint la limite de " .  $this->container->getParameter('mangopay.max_input') . "€ de crédit ou " . $this->container->getParameter('mangopay.max_output') . "€ de retrait vers votre compte. Afin de valider votre commande ou votre retrait, vous devez renseigner les informations suivantes pour valider votre identité bancaire. Une fois les éléments transmis à notre organisme bancaire, vous pourrez de nouveau valider vos commandes et demander des retraits sur votre compte."
            );
            return $this->redirectToRoute('user_account_wallet_kyc');
        }

        $this->get('mangopay_service')->freeWalletToTransferBank($user);

        return $this->redirectToRoute('user_account_wallet', ['transfer' => 'ok']);
    }

    /**
     * @Route("/portefeuille/rib", name="user_account_bank")
     * @Template("UserBundle:Account:bank.html.twig")
     * @param Request $request
     * @return array
     */
    public function accountBankAction(Request $request)
    {

        $data = [];
        $user = $this->getUser();
        $ibanBank = $this->get('mangopay_service')->getIbanBank(
            $user->getMangopayUserId()
        );

        if ($ibanBank) {

            $data = [
                'iban' => $ibanBank->Details->IBAN,
                'bic' => $ibanBank->Details->BIC,
                'name' => $ibanBank->OwnerName,
                'address_street' => $ibanBank->OwnerAddress->AddressLine1,
                'address_postal_code' => $ibanBank->OwnerAddress->PostalCode,
                'address_city' => $ibanBank->OwnerAddress->City,
                'address_country' => $ibanBank->OwnerAddress->Country
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
                'preferred_choices' => array('FR'),
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
     * @param Request $request
     * @param Order $order
     * @return array|RedirectResponse
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

        $this->get('order_listener')
            ->listen($request, $order);

        /**
         * Thread compose
         */
        $thread = $this->getDoctrine()
            ->getRepository('MessageBundle:Thread')
            ->findThreadByProductAndUsers($order->getProduct(), [
                $order->getUser()->getId(),
                $order->getProduct()->getUser()->getId()
            ]);

        if ($thread) {
            $form = $this->get('fos_message.reply_form.factory')->create($thread);
            $formHandler = $this->get('fos_message.reply_form.handler');

            if ($message = $formHandler->process($form)) {

                $this->container->get('session')->getFlashBag()->add('success', 'Message envoyé avec succès.');
                return $this->redirect($request->headers->get('referer'));
            }
        } else {

            $recipient = $order->getProduct()->getUser();

            if ($this->getUser() == $recipient) {
                $recipient = $order->getUser();
            }

            $form = $this->get('app.message')->processSentProductMessage($request, $order->getProduct(), $recipient);

            if ($form === true) {
                return $this->redirect($request->headers->get('referer'));
            }
        }

        /**
         * User Rating
         */
        if ($order->getStatus()->getName() == 'done') {

            if ($sale == true) {
                $userRating = $this->getDoctrine()
                    ->getRepository('UserBundle:Rating')
                    ->findRatedSeller($order);
            } else {
                $userRating = $this->getDoctrine()
                    ->getRepository('UserBundle:Rating')
                    ->findRatedBuyer($order);
            }

            if (!$userRating) {

                $formRating = $this->createForm(RatingType::class);
                $formRating->handleRequest($request);

                if ($formRating->isValid()) {

                    $rating = $formRating->getData();

                    $rating->setRatedUser(($sale) ? $order->getUser() : $order->getProduct()->getUser());
                    $rating->setAuthorUser((!$sale) ? $order->getUser() : $order->getProduct()->getUser());

                    $rating->setType(($sale) ? 'buyer' : 'seller');
                    $rating->setOrder($order);

                    $this->getDoctrine()->getManager()->persist($rating);
                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirect($request->headers->get('referer'));
                }

            }
        }

        if ($order->getDelivery()->getDeliveryMode()->isEmc() && $order->getStatus()->getName() == 'pending') {
            $delivery = $this->get('delivery.emc')->prepareDeliveryByOrder($order);
        }

        return [
            'order' => $order,
            'sale' => $sale,
            'thread' => $thread,
            'formMessage' => (isset($form)) ? $form->createView() : null,
            'disputeMessage' => (isset($form)) ? $form->createView() : null,
            'formRating' => (isset($formRating)) ? $formRating->createView() : null,
            'delivery' => (isset($delivery)) ? $delivery : null
        ];

    }

    /**
     * @Route("/produits", name="user_account_products")
     * @Template("UserBundle:Account:products.html.twig")
     * @param Request $request
     * @return array
     */
    public function productsAction(Request $request)
    {
        $status = $this->getDoctrine()
            ->getRepository("ProductBundle:Status")
            ->findByName([
                'awaiting',
                'published',
                'refused'
            ]);

        $repo = $this->getDoctrine()
            ->getRepository("ProductBundle:Product");

        $adapter = new ArrayAdapter($repo->findForUserAndStatus($this->getUser(), $status));
        $pagerfanta = new Pagerfanta($adapter);
        try {
            $pagerfanta->setMaxPerPage(10)->setCurrentPage($request->query->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return ['pager' => $pagerfanta];
    }

    /**
     * @Route("/produits/edition/{id}", name="user_account_products_edition")
     * @ParamConverter("product", class="ProductBundle:Product")
     * @Template("UserBundle:Account:product_edition.html.twig")
     * @param Request $request
     * @param Product $product
     * @return array
     */
    public function productEditionAction(Request $request, Product $product)
    {
        // Make sure user is the owner of the product
        $user = $this->getUser();
        if ($user->getId() != $product->getUser()->getId()) {
            throw new NotFoundHttpException('Current user is not the product owner');
        }
        if (!in_array($product->getStatus()->getName(), [
            'published',
            'awaiting'
        ])
        ) {
            throw new AccessDeniedHttpException('Product must have status "published" or "awaiting" in order to edit it');
        }

        // Copy ?
        if ($request->query->has('copy')) {
            $product = clone $product;
            foreach ($product->getAttributeValues() as $attr) {
                $attr = clone $attr;
                $product->addAttributeValue($attr);
                $attr->setProduct($product);
                $this->getDoctrine()->getManager()->persist($attr);
            }
            foreach ($product->getImages() as $image) {
                $image = clone $image;
                $image->setProduct($product);
                $this->getDoctrine()->getManager()->persist($image);
            }
            foreach ($product->getDeliveries() as $delivery) {

                if ($delivery->getDeliveryMode()->getType() == 'by_hand' || $delivery->getDeliveryMode()->getType() == 'shipping') {
                    $delivery = clone $delivery;
                    $delivery->setProduct($product);
                    $this->getDoctrine()->getManager()->persist($delivery);
                }
            }
            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();
            $this->container->get('session')->getFlashBag()->add('copy',
                'Votre produit "' . $product->getName() . '" a été copié avec succès. Vous pouvez modifier la copie ci-dessous');
            return $this->redirectToRoute('user_account_products_edition', ['id' => $product->getId()]);
        }

        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        $customFields = (new AttributesProduct($this->get('twig')))->getAttributes($product, $filters);

        if ($productForm->isValid()) {
            foreach ($product->getAttributeValues() as $attr) {
                $this->getDoctrine()->getManager()->remove($attr);
                $product->removeAttributeValue($attr);
            }

            if (isset($filters)) {
                foreach ($filters as $key => $filter) {
                    $value = $request->get('attribute-' . $key);

                    if ($request->get('attribute-' . $key)) {
                        $attributeValue = new AttributeValue();
                        $attributeValue->setProduct($product);

                        $referentialValue = $this->getDoctrine()->getRepository('ProductBundle:ReferentialValue')
                            ->find($value);
                        $attributeValue->setReferentialValue($referentialValue);

                        $attribute = $this->getDoctrine()->getRepository('ProductBundle:Attribute')->findOneByCode($key);
                        $attributeValue->setAttribute($attribute);
                        $product->addAttributeValue($attributeValue);

                        $this->getDoctrine()->getManager()->persist($attributeValue);
                    }
                }
            }

            // Handle images
            foreach ($product->getImages() as $image) {
                $product->removeImage($image);
            }
            for ($imageIdx = 0; $imageIdx < 5; $imageIdx++) {
                $imageId = $request->request->get('image' . $imageIdx);
                $image = $this->getDoctrine()->getRepository('ProductBundle:Image')->findOneById($imageId);
                if (isset($image)) {
                    $image->setProduct($product)->setSort($imageIdx);
                    $this->getDoctrine()->getManager()->persist($image);
                    $product->addImage($image);
                }
            }

            // Custom delivery
            $customDeliveryFee = $productForm->get('customDeliveryFee')->getData();
            $customDeliveryMode = $this->getDoctrine()->getRepository('OrderBundle:DeliveryMode')->findOneByCode('seller_custom');
            if (isset($customDeliveryMode)) {
                $customDelivery = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findOneBy([
                    'product' => $product,
                    'deliveryMode' => $customDeliveryMode
                ]);
                if (isset($customDeliveryFee)) {
                    if (!isset($customDelivery)) {
                        $customDelivery = new Delivery();
                        $customDelivery->setProduct($product);
                        $customDelivery->setDeliveryMode($customDeliveryMode);
                    }
                    $customDelivery->setFee($customDeliveryFee);
                    $this->getDoctrine()->getManager()->persist($customDelivery);
                    $this->getDoctrine()->getManager()->flush();
                } else {
                    if (isset($customDelivery)) {
                        $this->getDoctrine()->getManager()->remove($customDelivery);
                        $this->getDoctrine()->getManager()->flush();
                    }
                }
            }
            // By hand delivery
            $byHandDeliveryEnabled = $productForm->get('byHandDelivery')->getData();
            $byHandDeliveryMode = $this->getDoctrine()->getRepository('OrderBundle:DeliveryMode')->findOneByCode('by_hand');
            if (isset($byHandDeliveryMode)) {
                $byHandDelivery = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findOneBy([
                    'product' => $product,
                    'deliveryMode' => $byHandDeliveryMode
                ]);
                if ($byHandDeliveryEnabled) {
                    if (!isset($byHandDelivery)) {
                        $byHandDelivery = new Delivery();
                        $byHandDelivery->setProduct($product);
                        $byHandDelivery->setDeliveryMode($byHandDeliveryMode);
                        $byHandDelivery->setFee(0);
                        $this->getDoctrine()->getManager()->persist($byHandDelivery);
                        $this->getDoctrine()->getManager()->flush();
                    }
                } else if (isset($byHandDelivery)) {
                    $this->getDoctrine()->getManager()->remove($byHandDelivery);
                    $this->getDoctrine()->getManager()->flush();
                }
            }

            // Set status to awaiting
            $product->setUser($user);
            $awaitingStatus = $this->getDoctrine()
                ->getRepository("ProductBundle:Status")
                ->findOneByName('awaiting');
            $product->setStatus($awaitingStatus);

            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();

            $this->container->get('session')->getFlashBag()->add('success',
                'Votre produit "' . $product->getName() . '" a été modifié avec succès. Il est en cours de validation par notre équipe.');
            return $this->redirectToRoute('user_account_products');
        }

        return [
            'product' => $product,
            'productForm' => $productForm->createView(),
            'customFields' => $customFields
        ];
    }

    /**
     * @Route("/product", name="ajax_product_action", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function productAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['message' => 'You must be authentificated to update the product.'], 401);
        }
        if (!$request->request->has("product_id")) {
            return new JsonResponse(['message' => 'A product id (product_id) must be specified.'], 409);
        }
        $actions = [
            'remove',
            'update'
        ];
        if (!$request->request->has("action") || !in_array($action = $request->request->get('action'), $actions)) {
            return new JsonResponse(['message' => 'An action must be specified.'], 409);
        }

        $id = $request->request->get("product_id");
        $product = $this->getDoctrine()->getRepository("ProductBundle:Product")->findOneBy(['id' => $id]);
        if (!$product || $product->getUser() != $this->getUser()) {
            return new JsonResponse(['message' => 'Product not found.'], 404);
        }
        if (!in_array($product->getStatus()->getName(), [
            "awaiting",
            "published",
            "refused"
        ])
        ) {
            return new JsonResponse(['message' => 'The product can not be removed.'], 409);
        }
        if ($action == 'remove') {
            // If product is removed, update the product status to "sold"
            $status = $this->getDoctrine()->getRepository("ProductBundle:Status")->findOneBy(['name' => 'sold']);
            if (!$status) {
                return new JsonResponse(['message' => 'Status not found.'], 404);
            }
            $product->setStatus($status);
            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse(['message' => 'Product deleted']);
        } elseif ($action == 'update') {
            if (!$request->request->has("field")) {
                return new JsonResponse(['message' => 'A field to update must be specified.'], 409);
            }
            if ($request->request->get('field') == "price" && $request->request->has('price')) {
                $price = str_replace(",", ".", $request->request->get('price'));
                if (!is_numeric($price)) {
                    return new JsonResponse(['message' => 'The price field must be numeric.'], 410);
                }
                $product->setPrice($price);
                $this->getDoctrine()->getManager()->persist($product);
                $this->getDoctrine()->getManager()->flush();
                $service = $this->get('lexik_currency.formatter');
                $price = $service->format($product->getPrice(), $product->getCurrency()->getCode());

                return new JsonResponse([
                    'message' => 'Product updated',
                    'price' => $price
                ]);
            } else {
                return new JsonResponse(['message' => 'A price must be specified.'], 409);
            }
        }

    }

    /**
     * @Route("/portefeuille/kyc", name="user_account_wallet_kyc")
     * @Template("UserBundle:Account:wallet_kyc.html.twig")
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function walletKYCAction(Request $request)
    {
        $mangopayService = $this->get('mangopay_service');
        $mangopayUser = $mangopayService->getMangoPayUser($this->getUser()->getMangopayUserId());
        if ($mangopayUser->KYCLevel == "REGULAR") {
            return $this->redirectToRoute('user_account_wallet');
        }
        if ($mangopayUser->PersonType == 'NATURAL') {
            $form = $this->createForm(MangoPayKYCNaturalType::class);
        } else if ($mangopayUser->PersonType == 'LEGAL') {
            $form = $this->createForm(MangoPayKYCLegalType::class);
        } else {
            throw new \Exception('Bad person type for current mangopay user.');
        }

        $documents = $this->get('mangopay_service')->getListDocumentsByUserId($mangopayUser->Id);

        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($mangopayUser->PersonType == 'NATURAL') {
                $mangopayService->sendKYCRegularNaturalUser(
                    $mangopayUser,
                    $form->getData()
                );
            } else if ($mangopayUser->PersonType == 'LEGAL') {
                $mangopayService->sendKYCRegularLegalUser(
                    $mangopayUser,
                    $form->getData()
                );
            }
            $this->get('session')->getFlashBag()->add('kyc_success',
                "Vos informations ont bien été transmises, vous receverez par email une réponse dans un délai de 24 à 72 heures maximum");
            return $this->redirectToRoute('user_account_wallet_kyc');
        }

        return [
            'form' => $form->createView(),
            'documents' => $documents
        ];
    }

    /**
     * @Route("/supprimer-mon-compte", name="user_account_remove")
     * @param Request $request
     * @return RedirectResponse
     */
    public function removeAction(Request $request)
    {
        $products = $this->getDoctrine()
            ->getRepository('ProductBundle:Product')
            ->findByUser($this->getUser());

        $status = $this->getDoctrine()
            ->getRepository('ProductBundle:Status')
            ->findOneByName('deleted');

        foreach ($products as $product) {
            $product->setStatus($status);

            $this->getDoctrine()->getManager()->persist($product);
        }


        $user = $this->getUser();
        $user->setLocked(true);

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('fos_user_security_logout');
    }

    /**
     * @Route("/mes-adresses", name="user_addresses")
     * @param Request $request
     * @Template("UserBundle:Account:addresses.html.twig")
     * @throws \Exception
     * @return array|RedirectResponse
     */
    public function addressesAction(Request $request)
    {

        $addressForm = $this->get('user.address_form')->getForm(
            $request,
            $this->getUser(),
            true
        );

        if ($addressForm === true) {
            $this->addFlash('success', 'L\'adresse a bien été ajoutée');

            return $this->redirectToRoute('user_addresses');
        }

        $addresses =
            $this->getDoctrine()
                ->getRepository("LocationBundle:Address")
                ->findBy(['user' => $this->getUser()]);

        return [
            'addresses' => $addresses,
            'addAddressForm' => $addressForm->createView()
        ];
    }

    /**
     * @Route("/addresses", name="ajax_user_addresses", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addressAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['message' => 'You must be authentificated to update the address.'], 401);
        }

        $actions = ['remove'];

        if (!$request->request->has('action') || !in_array($action = $request->request->get('action'), $actions)) {
            return new JsonResponse(['message' => 'An action must be defined'], 409);
        }

        if (!$request->request->has('address_id')) {
            return new JsonResponse(['message' => 'An address_id must be defined'], 409);
        }

        $id = $request->request->get('address_id');

        $address = $this->getDoctrine()
            ->getRepository("LocationBundle:Address")
            ->findOneBy([
                'id' => $id,
                'user' => $this->getUser()
            ]);

        if (!$address) {
            return new JsonResponse(['message' => 'Address not found'], 404);
        }

        $this->getDoctrine()->getManager()->remove($address);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            [
                'message' => sprintf('Address %s has been deleted', $id)
            ]
        );
    }

    /**
     * @Route("/email/validation", name="email_validation")
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendEmailValidationAction(Request $request)
    {

        $this->get('fos_user.mailer.send')->sendConfirmationEmailMessage($this->getUser());

        $previousUri = $request->headers->get('referer');
        $previousUri = Request::create($previousUri, 'GET', array('asi' => 'send'))->getUri();

        return $this->redirect($previousUri);

    }

}
