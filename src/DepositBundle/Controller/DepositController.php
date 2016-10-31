<?php

namespace DepositBundle\Controller;

use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use ProductBundle\Entity\Image;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\AttributeValue;
use ProductBundle\Entity\ReferentialValue;
use LocationBundle\Entity\Address;
use LocationBundle\Form\AddressType;
use OrderBundle\Entity\Delivery;

/**
 * Class DepositController
 * @package DepositBundle\Controller
 * @Security("has_role('ROLE_USER')")
 * @Route("/vendez")
 */
class DepositController extends Controller
{
    /**
     * @Route("/etape-1", name="sell_category")
     * @Template("DepositBundle:Deposit:category.html.twig")
     * @return array
     */
    public function categoryAction()
    {
        $categories = $this->getDoctrine()->getRepository('ProductBundle:Category')
            ->findByParentHavingChildrenCache(null);

        return ['categories' => $categories];
    }

    /**
     * @Route("/deposit_postcategory", name="deposit_postcategory")
     * @Method({"POST"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function postCategoryAction(Request $request)
    {
        if ($request->request->has('category_id')) {
            $categoryId = $request->get('category_id');
            $category = $this->getDoctrine()->getRepository('ProductBundle:Category')->findOneBy(['id' => $categoryId]);
            if ($category) {
                $this->get('session')->set('deposit', ['category_id' => $categoryId]);

                return $this->redirectToRoute('sell_photos');
            } else {
                $this->addFlash("error", "La catégorie sélectionnée n'existe pas.");
            }
        } else {
            $this->addFlash("error", "Aucune catégorie n'a été sélectionnée.");
        }

        return $this->redirectToRoute('sell_category');
    }

    /**
     * @Route("/etape-2", name="sell_photos")
     * @Template("DepositBundle:Deposit:photos.html.twig")
     * @return array|RedirectResponse
     */
    public function photosAction()
    {
        $deposit = $this->get('session')->get('deposit');
        if (!$deposit || !isset($deposit['category_id'])) {

            return $this->redirectToRoute('sell_category');
        }

        return [];
    }

    /**
     * @Route("/deposit_postphotos", name="deposit_postphotos")
     * @Method({"POST"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function postPhotosAction(Request $request)
    {
        $picIds = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($request->get('image'.$i))) {
                $picIds[] = $request->get('image'.$i);
            }
        }
        if (count($picIds) > 0) {
            if ($this->get('session')->has('deposit')) {
                $deposit = $this->get('session')->get('deposit');
                $deposit['images'] = $picIds;
                $this->get('session')->set('deposit', $deposit);

                return $this->redirectToRoute('sell_description');
            }

            return $this->redirectToRoute('sell_photos');
        }
        $this->addFlash('error', "Aucune image n'a été ajoutée.");

        return $this->redirectToRoute('sell_photos');
    }

    /**
     * @Route("/etape-3", name="sell_description")
     * @Template("DepositBundle:Deposit:description.html.twig")
     * @return array|RedirectResponse
     */
    public function descriptionAction()
    {
        if ($this->get('session')->has('deposit')) {
            $deposit = $this->get('session')->get('deposit');

            // Make sure at least one image is set
            if (!isset($deposit['images'])) {
                return $this->redirectToRoute('sell_photos');
            }

            if (isset($deposit['category_id'])) {
                $category = $this->getDoctrine()->getRepository('ProductBundle:Category')
                    ->findOneBy(['id' => $deposit['category_id']]);
                if ($category) {
                    $attributes = $category->getAttributes();

                    $filters = [];
                    /** @var Attribute $attribute */
                    foreach ($attributes as $attribute) {
                        $code = $attribute->getCode();
                        $filters[$code] = [
                            'template' => $attribute->getAttributeDepositTemplate()->getName(),
                            'viewVars' => [
                                'label' => $attribute->getName(),
                                'id' => $attribute->getCode(),
                                'mandatory' => $attribute->getMandatory(),
                            ],
                        ];
                        $referential = $attribute->getReferential();
                        if ($referential) {
                            $filters[$code]['viewVars']['referentialName'] = $referential->getName();
                            $filters[$code]['viewVars']['referentialValues'] = $referential->getReferentialValues();
                        }
                    }

                    $customFields = [];
                    if (count($filters) > 0) {
                        foreach ($filters as $filter) {
                            $viewName = sprintf('DepositBundle:DepositFilters:%s.html.twig', $filter['template']);
                            $params = isset($filter['viewVars']) ? $filter['viewVars'] : [];
                            $customFields[] = $this->renderView($viewName, $params);
                        }
                    }

                    // Update designer list the dirty way
                    foreach ($customFields as $customFieldsIdx => $customField) {
                        if (strpos($customField, 'name="attribute-designer"') !== false) {
                            $str_replace_first = function($from, $to, $subject) {
                                $from = '/'.preg_quote($from, '/').'/';
                                return preg_replace($from, $to, $subject, 1);
                            };
                            $customFields[$customFieldsIdx] = $str_replace_first('</option>', '</option><option value="self">Moi-même</option>', $customField);
                        }
                    }

                    return ['customFields' => $customFields];
                }
            }
        }

        return $this->redirectToRoute('sell_photos');
    }

    /**
     * @Route("/deposit_postdescription", name="deposit_postdescription")
     * @Method({"POST"})
     * @param Request $request
     * @return array
     */
    public function postDescriptionAction(Request $request)
    {
        if ($this->get('session')->has('deposit')) {
            $deposit = $this->get('session')->get('deposit');

            $errors = $listAttributes = [];


            $fields = [
                'name' => "nom",
                'description' => "description",
                'width' => 'Largeur',
                'length' => 'Longueur',
                'height' => 'Hauteur',
                'weight' => 'Poids'
            ];

            foreach ($fields as $field => $fieldName) {
                if ($request->request->get($field, false) == false) {
                    $errors[] = sprintf("Le champ %s doit être renseigné.", $fieldName);
                } else {
                    $deposit[$field] = $request->request->get($field, 0);
                }
            }

            foreach ($request->request->all() as $field => $value) {
                if (strpos($field, 'attribute-') === 0 && $value !== '') {
                    list(, $fieldName) = explode('-', $field, 2);
                    $listAttributes[$fieldName] = $value;
                }
            }
            $category = $this->getDoctrine()
                ->getRepository('ProductBundle:Category')
                ->findOneBy(['id' => $deposit['category_id']]);


            if ($category) {
                $attributes = $category->getAttributes();
                foreach ($attributes as $attribute) {
                    $codeExist = array_key_exists($attribute->getCode(), $listAttributes);
                    if ($attribute->getMandatory() && (!$codeExist || empty($listAttributes[$attribute->getCode()]))) {
                        $errors[] = sprintf("Le champ %s doit être renseigné.", $attribute->getName());
                    }
                }

                if (count($errors) > 0) {
                    $this->get('session')->getFlashBag()->add('error', $errors);

                    return $this->redirectToRoute('sell_description');
                } else {
                    if (count($listAttributes) > 0) {
                        $attributeValues = $this->setAttributesInSession($attributes, $listAttributes);
                        if ($attributeValues) {
                            $deposit['attribute_values'] = $attributeValues;
                        }
                    }
                    $this->get('session')->set('deposit', $deposit);

                    return $this->redirectToRoute('sell_price');
                }
            }
        }

        return $this->redirectToRoute('sell_photos');
    }

    /**
     * @Route("/etape-4", name="sell_price")
     * @Template("DepositBundle:Deposit:price.html.twig")
     * @return array|RedirectResponse
     */
    public function priceAction()
    {
        $deposit = $this->get('session')->get('deposit');
        if (!$deposit || !isset($deposit['name'])) {

            return $this->redirectToRoute('sell_description');
        }

        // Get fee rates based on the seller
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $feeRateType = $user->getPro() == 1 ? 'pro' : 'individual';
        $feeRates = $em->getRepository('OrderBundle:FeeRate')->findBy(['type' => $feeRateType], ['minimum' => 'ASC']);
        $feeRatesArray = [];
        foreach ($feeRates as $feeRate) {
            $feeRatesArray[] = ['rate' => $feeRate->getRate(), 'min' => $feeRate->getMinimum()];
        }

        return [
            'fee_rates' => $feeRatesArray,
            'fixed_fee' => $this->getParameter('mangopay.fixed_fee'),
        ];
    }

    /**
     * @Route("/deposit_postprice", name="deposit_postprice")
     * @Method({"POST"})
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function postPriceAction(Request $request)
    {
        if ($this->get('session')->has('deposit')) {
            $deposit = $this->get('session')->get('deposit');

            $errors = [];
            foreach (['price', 'original_price', 'allow_offer'] as $field) {
                if ($request->request->has($field) && !empty($request->request->get($field))) {
                    if ($field == 'price' && $request->request->get($field) < 1) {
                        throw new \Exception('Price cannot be lower than 1.00');
                    } else {
                        if ($field == 'original_price' && $request->request->get($field) < 1) {
                            throw new \Exception('Price cannot be lower than 1.00');
                        }
                    }
                    $deposit[$field] = $request->request->get($field);
                } else {
                    if ($field == 'price') {
                        $errors[] = "Le champ prix de vente doit être renseigné.";
                    } elseif ($field == 'allow_offer') {
                        $deposit[$field] = 0;
                    }
                }
            }

            if (count($errors) > 0) {
                $this->get('session')->getFlashBag()->add('error', $errors);

                return $this->redirectToRoute('sell_price');
            } else {
                $this->get('session')->set('deposit', $deposit);

                return $this->redirectToRoute('sell_shipping');
            }
        } else {
            return $this->redirectToRoute('sell_description');
        }
    }

    /**
     * @Route("/etape-5", name="sell_shipping")
     * @Method({"GET"})
     * @Template("DepositBundle:Deposit:shipping.html.twig")
     * @return array|RedirectResponse
     */
    public function shippingAction(Request $request)
    {
        $deposit = $this->get('session')->get('deposit');
        if (!$deposit || !isset($deposit['price'])) {

            return $this->redirectToRoute('sell_price');
        }

        $addressForm = $this->get('user.address_form')->getForm(
            $request,
            $this->getUser(),
            true
        );

        // Load previous entered data in shipping form
        $shippingFormData = [];
        $shippingFormDataString = $this->get('session')->get('deposit_shipping_form_data');
        if ($shippingFormDataString) {
            $this->get('session')->remove('deposit_shipping_form_data');
            parse_str($shippingFormDataString, $shippingFormData);
            $shippingFormData = array_filter($shippingFormData);
            if (!isset($shippingFormData['by_hand'])) {
                $shippingFormData['by_hand_unselected'] = '1';
            }
        }

        $addresses = $this->getDoctrine()->getRepository("LocationBundle:Address")
            ->findBy(['user' => $this->getUser()], ['id' => 'DESC']);

        return [
            'addresses' => $addresses,
            'addAddressForm' => $addressForm->createView(),
            'shippingFormData' => $shippingFormData
        ];
    }

    /**
     * @Route("/etape-5", name="deposit_postshipping")
     * @Method({"POST"})
     * @param Request $request
     * @throws \Exception
     * @return RedirectResponse
     */
    public function postShippingAction(Request $request)
    {
        if ($this->get('session')->has('deposit')) {
            $deposit = $this->get('session')->get('deposit');

            if ($request->request->has('address')) {

                $addressForm = $this->get('user.address_form')->getForm(
                    $request,
                    $this->getUser(),
                    true
                );

                if ($addressForm === true) {
                    $this->get('session')->set('deposit', $deposit);
                    $this->addFlash('notice', 'Adresse ajoutée avec succès.');

                    $this->get('session')->set('deposit_shipping_form_data', $request->request->get('shipping-form-data'));

                    return $this->redirectToRoute('sell_shipping');
                }
            } else {
                $fields = [
                    'address_id' => "adresse",
                    'parcel_width' => "largeur du colis",
                    'parcel_length' => "longueur du colis",
                    'parcel_height' => "hauteur du colis",
                    'parcel_type' => "type de colis",
                ];


                $errors = [];
                foreach ($fields as $field => $label) {
                    if (!$request->request->has($field) || empty($request->request->get($field))) {
                        $errors[] = sprintf("Le champ %s doit être renseigné.", $label);
                        continue;
                    }


                    $fieldVal = $request->request->get($field);
                    $deposit['delivery'][$field] = $fieldVal;
                }


                $deliveryModes = $request->get('deliveryMode');

                if (count($request->get('deliveryMode')) == 0) {
                    $errors[] = "Aucun mode de livraison";
                }


                if (in_array('by_hand', $deliveryModes)) {
                    $deposit['delivery']['codes'][] = 'by_hand';
                }

                if (in_array('custom_seller', $deliveryModes)) {
                    $deposit['delivery']['codes'][] = 'seller_custom';
                    $deposit['delivery']['shipping_fees'] = $request->request->get('shipping_fees');
                }

                if (in_array('carrier_unik', $deliveryModes)) {
                    $deposit['delivery']['emc'] = true;
                }

                if (count($errors) > 0) {
                    $this->addFlash('error', $errors);

                    return $this->redirectToRoute('sell_shipping');
                } else {
                    if ($this->saveAction($deposit)) {
                        $this->get('session')->remove('deposit');
                        $this->get('session')->set('deposit_completed', true);
                    }

                    return $this->redirectToRoute('sell_thanks');
                }
            }
        } else {
            return $this->redirectToRoute('sell_price');
        }
    }

    /**
     * @param $deposit
     * @return bool
     */
    private function saveAction($deposit)
    {


        $product = new Product();
        $product
            ->setName($deposit['name'])
            ->setDescription($deposit['description'])
            ->setPrice($deposit['price'])
            ->setAllowOffer($deposit['allow_offer'])
            ->setWeight($deposit['weight']*1000)
            ->setLength($deposit['length'] / 100)
            ->setWidth($deposit['width'] / 100)
            ->setHeight($deposit['height'] / 100)
            ->setUser($this->getUser());

        if (isset($deposit['original_price'])) {
            $product->setOriginalPrice($deposit['original_price']);
        }

        $currency = $this->getDoctrine()
            ->getRepository('ProductBundle:Currency')
            ->findOneBy(['code' => 'EUR']);
        if ($currency) {
            $product->setCurrency($currency);
        }

        $category = $this->getDoctrine()
            ->getRepository('ProductBundle:Category')
            ->findOneBy(['id' => $deposit['category_id']]);

        if ($category) {
            $product->setCategory($category);
        }

        $status = $this->getDoctrine()
            ->getRepository('ProductBundle:Status')
            ->findOneBy(['name' => 'awaiting']);

        if ($status) {
            $product->setStatus($status);
        }

        $address = $this->getDoctrine()
            ->getRepository('LocationBundle:Address')
            ->findOneBy(['id' => $deposit['delivery']['address_id']]);

        if ($address) {
            $product->setAddress($address);
        }

        if (isset($deposit['delivery']['codes']) && count($deposit['delivery']['codes']) > 0) {

            $deliveryModes = $this->getDoctrine()
                ->getRepository('OrderBundle:DeliveryMode')
                ->findBy(['code' => $deposit['delivery']['codes']]);

            foreach ($deliveryModes as $deliveryMode) {
                $delivery = new Delivery();
                if ($deliveryMode->getCode() == 'seller_custom') {
                    $delivery->setFee($deposit['delivery']['shipping_fees']);
                }
                $delivery->setDeliveryMode($deliveryMode);
                $product->addDelivery($delivery);
                $this->getDoctrine()->getManager()->persist($delivery);
            }
        }

        $product->setParcelHeight($deposit['delivery']['parcel_height']);
        $product->setParcelWidth($deposit['delivery']['parcel_width']);
        $product->setParcelLength($deposit['delivery']['parcel_length']);
        $product->setParcelType($deposit['delivery']['parcel_type']);

        if (isset($deposit['delivery']['emc'])) {
            $product->setEmc(true);
        }

        // Associate product to every image
        if (isset($deposit['images']) && count($deposit['images']) > 0) {
            $images = $this->getDoctrine()->getRepository('ProductBundle:Image')->findBy(['id' => $deposit['images']]);
            if (isset($images)) {
                $i = 0;
                foreach ($images as $image) {
                    $image->setProduct($product)->setSort($i);
                    $this->getDoctrine()->getManager()->persist($image);
                    $i++;
                }
            }
        }

        $this->getDoctrine()->getManager()->persist($product);
        $this->getDoctrine()->getManager()->flush();

        if (isset($deposit['attribute_values'])) {
            $this->saveProductAttributes($deposit['attribute_values'], $product);
        }

        return true;
    }

    /**
     * @Route("/etape-6", name="sell_thanks")
     * @Template("DepositBundle:Deposit:thanks.html.twig")
     * @return array|RedirectResponse
     */
    public function thanksAction()
    {
        $depositCompleted = $this->get('session')->get('deposit_completed', false);
        if (!$depositCompleted) {
            return $this->redirectToRoute('homepage');
        }
        $this->get('session')->remove('deposit_completed');

        return [];
    }

    /**
     * @Route("/deposit_subcategories", name="deposit_subcategories", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getSubCategoriesAction(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategs = [];
        if (isset($categoryId)) {
            $category = $this->getDoctrine()->getRepository('ProductBundle:Category')->findOneBy(['id' => $categoryId]);
            $subcategs = $this->get('product_bundle.category_service')->getSubCategories($category);
        }

        if (count($subcategs) == 0) {
            return new JsonResponse(['message' => 'No subcategory found.'], 404);
        }
        if (count($subcategs) > 0) {
            return new JsonResponse(['message' => 'Subcategories found.', 'subcategories' => $subcategs], 201);
        }

        return new JsonResponse(['message' => 'An error occured.'], 500);
    }

    /**
     * @Route("/upload_picture", name="upload_picture", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPictureAction(Request $request)
    {
        try {
            $img = new Image();
            $img->setImageFile($request->files->get('files')[0]);
            $this->getDoctrine()->getManager()->persist($img);
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => "An error occured while uploading."], 500);
        }

        return new JsonResponse(['message' => "Image successfully uploaded.", 'pic' => ['id' => $img->getId()]], 201);
    }

    /**
     * @param $attributes
     * @param $listAttributes
     * @return array|bool
     */
    private function setAttributesInSession($attributes, $listAttributes)
    {
        // Reset possible attribute values
        $attribute_values = [];
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if (!isset($listAttributes[$attribute->getCode()]) && !$attribute->getMandatory()) {
                continue;
            }
            $postValue = $listAttributes[$attribute->getCode()];
            $referential = $attribute->getReferential();
            $referentialValues = [];
            if ($referential) {
                foreach ($referential->getReferentialValues() as $referentialVal) {
                    $referentialValues[$referentialVal->getId()] = $referentialVal->getValue();
                }
            }

            if (count($referentialValues) > 0 && in_array($postValue, array_keys($referentialValues))) {
                // Case for existing value from referential
                $attribute_values[$attribute->getId()]['referential_value_id'] = $postValue;
            } else {
                // Case for free value
                $attributeTypeName = $attribute->getAttributeType()->getName();
                // Cast value in the correct type
                settype($postValue, $attributeTypeName);
                // Fix exception to correctly associate attribute type with attribute value fields
                $attributeTypeName = $attributeTypeName == 'string' ? 'text' : $attributeTypeName;
                $attribute_values[$attribute->getId()][$attributeTypeName.'_value'] = $postValue;
            }
        }

        return (count($attribute_values) > 0) ? $attribute_values : false;
    }

    /**
     * @param $attributeValues
     * @param $product
     */
    private function saveProductAttributes($attributeValues, $product)
    {
        if (isset($attributeValues) && count($attributeValues) > 0) {
            foreach ($attributeValues as $attrId => $attrValues) {
                $attributeValue = new AttributeValue();

                if (isset($attrValues['referential_value_id'])) {
                    $referentialValue = $this->getDoctrine()->getRepository('ProductBundle:ReferentialValue')
                        ->findOneBy(['id' => $attrValues['referential_value_id']]);
                    if (isset($referentialValue)) {
                        $attributeValue->setReferentialValue($referentialValue);
                    }
                }

                $attributeValue->setProduct($product);
                $attribute = $this->getDoctrine()->getRepository('ProductBundle:Attribute')
                    ->findOneBy(['id' => $attrId]);
                if ($attribute) {
                    $attributeValue->setAttribute($attribute);
                }

                if (isset($attrValues['text_value'])) {
                    $attributeValue->setTextValue($attrValues['text_value']);
                }
                if (isset($attrValues['boolean_value'])) {
                    $attributeValue->setBooleanValue($attrValues['boolean_value']);
                }
                if (isset($attrValues['integer_value'])) {
                    $attributeValue->setIntegerValue($attrValues['integer_value']);
                }
                if (isset($attrValues['float_value'])) {
                    $attributeValue->setFloatValue($attrValues['float_value']);
                }

                // Special case for designer (self)
                if (isset($attrValues['text_value']) && $attrValues['text_value'] == 'self') {
                    $designerAttribute = $this->getDoctrine()->getRepository('ProductBundle:Attribute')->findOneById($attrId);
                    if (isset($designerAttribute)) {
                        $user = $this->getUser();
                        $value = trim($user->getFirstname().' '.$user->getLastname());
                        $designerReferentialValues = $designerAttribute->getReferential()->getReferentialValues();
                        $flatdesignerReferentialValues = [];
                        foreach ($designerReferentialValues as $designerReferentialValue) {
                            $flatdesignerReferentialValues[] = $designerReferentialValue->getValue();
                        }
                        if (!in_array($value, $flatdesignerReferentialValues)) {
                            // Add referential value
                            $referentialValue = new ReferentialValue();
                            $referentialValue->setValue($value);
                            $referentialValue->addReferential($designerAttribute->getReferential());
                            $this->getDoctrine()->getManager()->persist($referentialValue);
                            $this->getDoctrine()->getManager()->flush();
                        }
                        else {
                            // Set referential value
                            foreach ($designerReferentialValues as $designerReferentialValue) {
                                if ($designerReferentialValue->getValue() == $value) {
                                    $referentialValue = $designerReferentialValue;
                                }
                            }
                        }
                        $attributeValue->setTextValue(null);
                        $attributeValue->setReferentialValue($referentialValue);
                    }
                }

                $this->getDoctrine()->getManager()->persist($attributeValue);
                $this->getDoctrine()->getManager()->flush();
            }
        }
    }
}