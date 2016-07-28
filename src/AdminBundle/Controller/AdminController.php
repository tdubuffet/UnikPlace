<?php

namespace AdminBundle\Controller;

use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;


class AdminController extends BaseAdminController
{
    public function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    public function prePersistUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }

    public function preUpdateUserEntity($user)
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }


    /**
     * @Route(path = "/admin/moderate", name = "admin_moderate")
     * @Template("AdminBundle:Admin:moderate.html.twig")
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     */
    public function moderateAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('ProductBundle:Product');
        $products = $repository->findBy(['status' => 1]);

        if ($request->request->has('status')) {
            $id = $request->request->get('id');
            $product = $this->getDoctrine()->getRepository("ProductBundle:Product")->findOneBy(['id' => $id]);
            if (!$product) {
                return $this->redirectToRoute("admin_moderate", ['page' => $request->query->get('page', 1)]);
            }
            $status = $request->request->get("status");
            $status = $this->getDoctrine()->getRepository("ProductBundle:Status")->findOneBy(['name' => $status]);
            $product->setStatus($status);
            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();
            if ($status->getName() == "refused") {
                $this->get('mailer_sender')->sendRefusedProductEmailMessage($product);
            } elseif ($status->getName() == "published") {
                $this->get('mailer_sender')->sendAcceptedProductEmailMessage($product);
            }

            return $this->redirectToRoute("admin_moderate");
        }
        $page = $request->query->get('page', 1);

        $adapter = new ArrayAdapter($products);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(1);
        if ($page > $pagerfanta->getNbPages()) {
            return $this->redirectToRoute("admin_moderate");
        }
        $pagerfanta->setCurrentPage($page);
        /** @var Product $product */
        $product = $pagerfanta->getCurrentPageResults()[0];
        $attributes = $this->get('product_bundle.product_attribute_service')->getAttributesFromProduct($product);

        return ['product' => $product, 'attributes' => $attributes, 'pager' => $pagerfanta];
    }
}
