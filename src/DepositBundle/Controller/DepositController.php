<?php

namespace DepositBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Intervention\Image\ImageManager;
use ProductBundle\Entity\Image;
use ProductBundle\Entity\Product;
use ProductBundle\Entity\AttributeValue;
use LocationBundle\Entity\Address;
use LocationBundle\Form\AddressType;

/**
 * Class DepositController
 * @package DepositBundle\Controller
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/vendez")
 */
class DepositController extends Controller
{
    /**
     * @Route("/etape-1", name="sell_category")
     * @Template("DepositBundle:Deposit:category.html.twig")
     */
    public function categoryAction()
    {
        $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
        $categories = $repository->findBySlug(array('maison-deco', 'art-culture', 'loisirs-multimedia'));

        return array('categories' => $categories);
    }

    /**
     * @Route("/deposit_postcategory", name="deposit_postcategory")
     * @Method({"POST"})
     */
    public function postCategoryAction(Request $request)
    {
        $categoryId = $request->get('category_id');
        $session = new Session();

        if (isset($categoryId) && !empty($categoryId)) {
            $category = $this->getDoctrine()->getRepository('ProductBundle:Category')->findOneById($categoryId);
            if ($category) {
                $deposit = array('category_id' => $categoryId);
                $session->set('deposit', $deposit);
                return $this->redirectToRoute('sell_photos');
            } else {
                $session->getFlashBag()->add('error', "La catégorie sélectionnée n'existe pas.");
            }
        } else {
            $session->getFlashBag()->add('error', "Aucune catégorie n'a été sélectionnée.");
        }
        return $this->redirectToRoute('sell_category');
    }

    /**
     * @Route("/etape-2", name="sell_photos")
     * @Template("DepositBundle:Deposit:photos.html.twig")
     */
    public function photosAction()
    {
    }

    /**
     * @Route("/deposit_postphotos", name="deposit_postphotos")
     * @Method({"POST"})
     */
    public function postPhotosAction(Request $request)
    {
        $session = $this->get('session');

        $picIds = array();
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($request->get('image'.$i))) {
                $picIds[] = $request->get('image'.$i);
            }
        }
        if (count($picIds) > 0) {
            if ($session->has('deposit')) {
                $deposit = $session->get('deposit');
                $deposit['images'] = $picIds;
                $session->set('deposit', $deposit);
                return $this->redirectToRoute('sell_description');
            } else {
                return $this->redirectToRoute('sell_photos');
            }
        } else {
            $session->getFlashBag()->add('error', "Aucune image n'a été ajoutée.");
        }

        return $this->redirectToRoute('sell_photos');
    }

    /**
     * @Route("/etape-3", name="sell_description")
     * @Template("DepositBundle:Deposit:description.html.twig")
     */
    public function descriptionAction()
    {
        $session = $this->get('session');

        if ($session->has('deposit')) {
            $deposit = $session->get('deposit');

            if (isset($deposit['category_id'])) {
                $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
                $category = $repository->findOneById($deposit['category_id']);
                if ($category) {
                    $attributes = $category->getAttributes();

                    $filters = [];

                    foreach ($attributes as $attribute) {
                        $template = $attribute->getAttributeDepositTemplate();
                        $filters[$attribute->getCode()] = [
                            'template' => $template->getName(),
                            'viewVars' => [
                                'label'     => $attribute->getName(),
                                'id'        => $attribute->getCode(),
                                'mandatory' => $attribute->getMandatory()
                            ]
                        ];
                        $referential = $attribute->getReferential();
                        if (isset($referential)) {
                            $filters[$attribute->getCode()]['viewVars']['referentialName'] = $referential->getName();
                            $filters[$attribute->getCode()]['viewVars']['referentialValues'] = $referential->getReferentialValues();
                        }
                    }

                    $customFields = [];
                    if (count($filters) > 0) {
                        foreach ($filters as $filter) {
                            $customFields[] = $this->renderView(
                                'DepositBundle:DepositFilters:'.$filter['template'].'.html.twig',
                                isset($filter['viewVars']) ? $filter['viewVars'] : []
                            );
                        }
                    }

                    return array('customFields' => $customFields);
                }
            }
        } else {
            return $this->redirectToRoute('sell_photos');
        }
    }

    /**
     * @Route("/deposit_postdescription", name="deposit_postdescription")
     * @Method({"POST"})
     */
    public function postDescriptionAction(Request $request)
    {
        $session = $this->get('session');

        if ($session->has('deposit')) {
            $deposit = $session->get('deposit');

            $errors = $listAttributes = [];
            foreach (['name' => "nom", 'description' => "description"] as $field => $fieldName) {
                if (!$request->get($field) || empty($request->get($field))) {
                    $errors[] = "Le champ ".$fieldName." doit être renseigné.";
                } else {
                    $deposit[$field] = $request->get($field);
                }
            }
            foreach ($request->request->all() as $field => $value) {
                if (strpos($field, 'attribute-') === 0) {
                    list(, $fieldName) = explode('-', $field, 2);
                    $listAttributes[$fieldName] = $value;
                }
            }

            $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
            $category = $repository->findOneById($deposit['category_id']);
            if ($category) {
                $attributes = $category->getAttributes();
                foreach ($attributes as $attribute) {
                    if ($attribute->getMandatory() && (!array_key_exists($attribute->getCode(), $listAttributes) || empty($listAttributes[$attribute->getCode()]))) {
                        $errors[] = "Le champ ".$attribute->getName()." doit être renseigné.";
                    }
                }

                if (count($errors) > 0) {
                    $session->getFlashBag()->add('error', $errors);
                    return $this->redirectToRoute('sell_description');
                } else {
                    $attributeValues = $this->setAttributesInSession($attributes, $listAttributes);
                    if ($attributeValues) {
                        $deposit['attribute_values'] = $attributeValues;
                        $session->set('deposit', $deposit);
                        return $this->redirectToRoute('sell_price');
                    }
                }
            } else {
                return $this->redirectToRoute('sell_photos');
            }
        } else {
            return $this->redirectToRoute('sell_photos');
        }
    }

    /**
     * @Route("/etape-4", name="sell_price")
     * @Template("DepositBundle:Deposit:price.html.twig")
     */
    public function priceAction()
    {
    }

    /**
     * @Route("/deposit_postprice", name="deposit_postprice")
     * @Method({"POST"})
     */
    public function postPriceAction(Request $request)
    {
        $session = $this->get('session');

        if ($session->has('deposit')) {
            $deposit = $session->get('deposit');

            $errors = [];
            foreach (['price', 'original_price', 'allow_offer'] as $field) {
                if ($request->get($field) && !empty($request->get($field))) {
                    $deposit[$field] = $request->get($field);
                } else {
                    if ($field == 'price') {
                        $errors[] = "Le champ prix de vente doit être renseigné.";
                    } elseif ($field == 'allow_offer') {
                        $deposit[$field] = 0;
                    }
                }
            }

            if (count($errors) > 0) {
                $session->getFlashBag()->add('error', $errors);
                return $this->redirectToRoute('sell_price');
            } else {
                $session->set('deposit', $deposit);
                return $this->redirectToRoute('sell_shipping');
            }
        } else {
            return $this->redirectToRoute('sell_description');
        }
    }

    /**
     * @Route("/etape-5", name="sell_shipping")
     * @Template("DepositBundle:Deposit:shipping.html.twig")
     */
    public function shippingAction()
    {
        $address = new Address;
        $addAddressForm = $this->createForm(AddressType::class, $address);

        $addresses = $this->getUser()->getAddresses();
        return array('addresses' => $addresses, 'addAddressForm' => $addAddressForm->createView());
    }

    /**
    * @Route("/deposit_postshipping", name="deposit_postshipping")
    * @Method({"POST"})
    */
    public function postShippingAction(Request $request)
    {
        $session = $this->get('session');

        if ($session->has('deposit')) {
            $deposit = $session->get('deposit');

            if($request->request->has('address')) {
                $address = new Address;
                $form = $this->createForm(AddressType::class, $address);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $cityId = $request->request->get('address')['city'];
                    // Get city from id
                    $city = $this->getDoctrine()->getRepository('LocationBundle:City')->findOneById($cityId);
                    if (!isset($city)) {
                        throw new \Exception('Cannot find city.');
                    }
                    $address->setCity($city);
                    $address->setUser($this->getUser());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($address);
                    $em->flush();

                    // Store user phone in session
                    if ($request->get('phone')) {
                        $deposit['phone'] = $request->request->get('phone');
                        $session->set('deposit', $deposit);
                    }

                    $session->getFlashBag()->add('notice', 'Adresse ajoutée avec succès.');
                    return $this->redirectToRoute('sell_shipping');
                }
            } else {
                $fields = ['weight' => "poids", 'address_id' => "adresse"];

                $errors = [];
                foreach ($fields as $field => $label) {
                    if (!$request->get($field) || empty($request->get($field))) {
                        $errors[] = "Le champ ".$label." doit être renseigné.";
                    } else {
                        if ($field == 'weight') {
                            if ($request->get($field) < 30) {
                                $deposit['delivery']['codes'][] = 'parcel';
                            }
                            $deposit['delivery'][$field] = ($request->get($field) * 1000); // transform Kg to g
                        } else {
                            $deposit['delivery'][$field] = $request->get($field);
                        }
                    }
                }

                if ($request->get('by_hand')) {
                    $deposit['delivery']['codes'][] = 'by_hand';
                }

                if ($request->get('shipping_fees') && !empty($request->get('shipping_fees'))) {
                    $deposit['delivery']['shipping_fees'] = $request->get('shipping_fees');
                }

                if (count($errors) > 0) {
                    $session->getFlashBag()->add('error', $errors);
                    return $this->redirectToRoute('sell_shipping');
                } else {
                    if ($this->saveAction($deposit)) $session->remove('deposit');

                    return $this->redirectToRoute('sell_thanks');
                }
            }
        } else {
            return $this->redirectToRoute('sell_price');
        }
    }

    private function saveAction($deposit) {
        // Create product
        $product = new Product();
        $product->setName($deposit['name']);
        $product->setDescription($deposit['description']);
        $product->setPrice($deposit['price']);
        $product->setAllowOffer($deposit['allow_offer']);
        $product->setWeight($deposit['delivery']['weight']);
        if (isset($deposit['shipping_fees'])) $product->setShippingFees($deposit['delivery']['shipping_fees']);
        if (isset($deposit['original_price'])) $product->setOriginalPrice($deposit['original_price']);

        $currency = $this->getDoctrine()->getRepository('ProductBundle:Currency')->findOneByCode('EUR');
        if (isset($currency)) $product->setCurrency($currency);

        $category = $this->getDoctrine()->getRepository('ProductBundle:Category')->findOneById($deposit['category_id']);
        if (isset($category)) $product->setCategory($category);

        $status = $this->getDoctrine()->getRepository('ProductBundle:Status')->findOneByName('awaiting');
        if (isset($status)) $product->setStatus($status);

        $address = $this->getDoctrine()->getRepository('LocationBundle:Address')->findOneById($deposit['delivery']['address_id']);
        if (isset($address)) $product->setAddress($address);

        if (isset($deposit['delivery']['codes']) && count($deposit['delivery']['codes']) > 0) {
            $deliveries = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findByCode($deposit['delivery']['codes']);
            if (isset($deliveries)) {
                foreach ($deliveries as $delivery) $product->addDelivery($delivery);
            }
        }

        $product->setUser($this->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        // Associate product to every image
        if (isset($deposit['images']) && count($deposit['images']) > 0) {
            $images = $this->getDoctrine()->getRepository('ProductBundle:Image')->findById($deposit['images']);
            if (isset($images)) {
                foreach ($images as $image) $image->setProduct($product);
            }
        }

        $this->saveProductAttributes($deposit['attribute_values'], $product);

        return true;
    }

    /**
     * @Route("/etape-6", name="sell_thanks")
     * @Template("DepositBundle:Deposit:thanks.html.twig")
     */
    public function thanksAction()
    {
        $session = $this->get('session');

        if ($session->has('deposit')) {
            $session->remove('deposit');
        }
    }

    /**
     * @Route("/deposit_subcategories", name="deposit_subcategories")
     * @Method({"POST"})
     */
    public function getSubCategoriesAction(Request $request)
    {
        $categoryId = $request->get('category_id');
        if (!isset($categoryId)) {
            return new JsonResponse(array('message' => 'A category id (category_id) must be specified.'), 409);
        }

        $repository = $this->getDoctrine()->getRepository('ProductBundle:Category');
        $subCategories = $repository->findAll();

        $subcategs = array();
        foreach ($subCategories as $subcateg) {
            if ($subcateg->getParent() != null) {
                if ($subcateg->getParent()->getId() == $categoryId) {
                    $subcategs[$subcateg->getId()] = array(
                        'id' => $subcateg->getId(),
                        'name' => $subcateg->getName()
                    );
                }
            }
        }

        foreach ($subCategories as $subcateg) {
            if ($subcateg->getParent() != null) {
                if (in_array($subcateg->getParent()->getId(), array_keys($subcategs))) {
                    $subcategs[$subcateg->getParent()->getId()]['children'][$subcateg->getId()] = array(
                        'id' => $subcateg->getId(),
                        'name' => $subcateg->getName()
                    );
                }
            }
        }

        if (count($subcategs) == 0) {
            return new JsonResponse(array('message' => 'No subcategory found.'), 404);
        }
        if (count($subcategs) > 0) {
            return new JsonResponse(array('message' => 'Subcategories found.', 'subcategories' => $subcategs), 201);
        }

        return new JsonResponse(array('message' => 'An error occured.'), 500);
    }

    /**
     * @Route("/upload_picture", name="upload_picture")
     * @Method({"POST"})
     */
    public function uploadPictureAction(Request $request)
    {
        $file = $request->files->get('files');

        try {
            $img = new Image();
            $img->setImageFile($file[0]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($img);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array('message' => "An error occured while uploading."), 500);
        }

        $pic = array(
            'id' => $img->getId()
        );

        return new JsonResponse(array('message' => "Image successfully uploaded.", 'pic' => $pic), 201);
    }

    private function setAttributesInSession($attributes, $listAttributes) {
        // Reset possible attribute values
        $attribute_values = [];
        foreach ($attributes as $attribute) {
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
                if ($attributeTypeName == 'string') $attributeTypeName = 'text';

                $attribute_values[$attribute->getId()][$attributeTypeName.'_value'] = $postValue;
            }
        }

        return (count($attribute_values) > 0) ? $attribute_values : false;
    }

    private function saveProductAttributes($attributeValues, $product) {
        if (isset($attributeValues) && count($attributeValues) > 0) {
            foreach ($attributeValues as $attrId => $attrValues) {
                $attributeValue = new AttributeValue();

                if (isset($attrValues['referential_value_id'])) {
                    $referentialValue = $this->getDoctrine()->getRepository('ProductBundle:ReferentialValue')->findOneById($attrValues['referential_value_id']);
                    if (isset($referentialValue)) {
                        $attributeValue->setReferentialValue($referentialValue);
                    }
                }

                $attributeValue->setProduct($product);

                $attribute = $this->getDoctrine()->getRepository('ProductBundle:Attribute')->findOneById($attrId);
                if (isset($attribute)) {
                    $attributeValue->setAttribute($attribute);
                }

                if (isset($attrValues['text_value'])) $attributeValue->setTextValue($attrValues['text_value']);
                if (isset($attrValues['boolean_value'])) $attributeValue->setBooleanValue($attrValues['boolean_value']);
                if (isset($attrValues['integer_value'])) $attributeValue->setIntegerValue($attrValues['integer_value']);
                if (isset($attrValues['float_value'])) $attributeValue->setFloatValue($attrValues['float_value']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($attributeValue);
                $em->flush();
            }
        }
    }
}