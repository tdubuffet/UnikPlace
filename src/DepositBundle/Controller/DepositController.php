<?php

namespace DepositBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

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
    public function postCategoryAction(Request $request) {
        //var_dump($request->request->all());
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
        // TODO
    }

    /**
     * @Route("/vendez/etape-4", name="sell_description")
     * @Template("DepositBundle:Deposit:description.html.twig")
     */
    public function descriptionAction()
    {
        // TODO
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
}