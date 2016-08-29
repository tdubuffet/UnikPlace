<?php

namespace Admin2Bundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UserBundle\Entity\User;
use UserBundle\Form\UserAdminFormType;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="ad2_user_list")
     */
    public function listAction(Request $request)
    {


        $search = $request->get('search');
        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('u')
            ->from('\UserBundle\Entity\User', 'u');

        if ($search) {
            $query->where('u.id LIKE :search or u.username LIKE :search OR u.firstname LIKE :search OR u.lastname LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:User:list.html.twig', [
            'users' => $pagerfanta
        ]);
    }

    /**
     * @Route("/{id}", name="ad2_user_show")
     */
    public function showAction(Request $request, User $user)
    {


        $threads = $this->getDoctrine()->getRepository('MessageBundle:Thread')->findThreadByUser($user);



        return $this->render('Admin2Bundle:User:show.html.twig', [
            'user'      => $user,
            'threads'   => $threads
        ]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_user_edit")
     */
    public function editAction(Request $request, User $user)
    {

        $form = $this->createForm(UserAdminFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_user_show', ['id' => $user->getId()]);

        }


        return $this->render('Admin2Bundle:User:edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
