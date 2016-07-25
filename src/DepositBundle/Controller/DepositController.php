<?php

namespace DepositBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Intervention\Image\ImageManager;
use ProductBundle\Entity\Image;

class DepositController extends Controller
{
    /**
     * @Route("/vendez", name="sell_signin")
     * @Template("DepositBundle:Deposit:signin.html.twig")
     */
    public function signinAction()
    {
        // TODO
    }

    /**
     * @Route("/vendez/etape-2", name="sell_category")
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
                // TODO: control to check if correct deposit step
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
     * @Route("/vendez/etape-3", name="sell_photos")
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
                // TODO: control to check if correct deposit step
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
     * @Route("/vendez/etape-4", name="sell_description")
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
                    foreach ($filters as $filter) {
                        $customFields[] = $this->renderView(
                            'DepositBundle:DepositFilters:'.$filter['template'].'.html.twig',
                            isset($filter['viewVars']) ? $filter['viewVars'] : []
                        );
                    }

                    return array('customFields' => $customFields);
                } else {
                    return $this->redirectToRoute('sell_photos');
                }
            } else {
                return $this->redirectToRoute('sell_photos');
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
            // TODO: control to check if correct deposit step
            $deposit = $session->get('deposit');

            $errors = $listAttributes = [];
            foreach (['name' => "nom", 'description' => "description"] as $field => $fieldName) {
                if (!$request->get($field) || empty($request->get($field))) {
                    $errors[] = "Le champ ".$fieldName." doit être renseigné.";
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

                if (count($errors) <= 0) {
                    // Reset possible attribute values
                    $deposit['attribute_values'] = [];

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
                            $deposit['attribute_values'][$attribute->getId()]['referential_value_id'] = $postValue;
                        } else {
                            // Case for free value
                            $attributeTypeName = $attribute->getAttributeType()->getName();

                            // Cast value in the correct type
                            settype($postValue, $attributeTypeName);

                            // Fix exception to correctly associate attribute type with attribute value fields
                            if ($attributeTypeName == 'string') $attributeTypeName = 'text';

                            $deposit['attribute_values'][$attribute->getId()][$attributeTypeName.'_value'] = $postValue;
                        }
                    }

                    if (count($deposit['attribute_values']) > 0) {
                        $session->set('deposit', $deposit);
                        return $this->redirectToRoute('sell_price');
                    } else {
                        return $this->redirectToRoute('sell_photos');
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
     * @Route("/vendez/etape-5", name="sell_price")
     * @Template("DepositBundle:Deposit:price.html.twig")
     */
    public function priceAction()
    {
        // TODO
    }

    /**
     * @Route("/vendez/etape-6", name="sell_shipping")
     * @Template("DepositBundle:Deposit:shipping.html.twig")
     */
    public function shippingAction()
    {
        // TODO
    }

    /**
     * @Route("/vendez/etape-7", name="sell_thanks")
     * @Template("DepositBundle:Deposit:thanks.html.twig")
     */
    public function thanksAction()
    {
        // TODO
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
}