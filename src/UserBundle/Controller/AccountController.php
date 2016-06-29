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
}
