<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 28/07/16
 * Time: 10:48
 */

namespace AppBundle\Controller;


use AppBundle\Form\ContactForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
    /**
     * @Route("/contact", name="contact")
     * @Template("AppBundle:default:contact.html.twig")
     * @param Request $request
     * @return array
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(ContactForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['ip'] = $request->getClientIp();
            $data['user_agent'] = $request->headers->get('User-Agent');
            $data['is_logged_username'] = $this->isGranted("ROLE_USER");
            $this->get('mailer_sender')->sendContactEmailMessage($data);
            $this->addFlash("success", "Votre message a bien été envoyé");

            return $this->redirectToRoute("contact");
        }

        return ['form' => $form->createView()];
    }

}