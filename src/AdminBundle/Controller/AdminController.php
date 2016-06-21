<?php

namespace AdminBundle\Controller;

use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


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
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function moderateAction()
    {
        $repository = $this->getDoctrine()->getRepository('ProductBundle:Product');
        $products = $repository->findByStatus(1);
        return ['products' => $products];
    }
}
