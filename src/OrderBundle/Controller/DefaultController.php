<?php

namespace OrderBundle\Controller;

use OrderBundle\Entity\OrderProposal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/offre-produit/{id}", name="offer_validation")
     * @ParamConverter("proposal", class="OrderBundle:OrderProposal")
     * @Template("OrderBundle:Default:offer_validation.html.twig")
     * @Security("user.getEmail() == proposal.getProduct().getUser().getEmail()")
     * @param Request $request
     * @param OrderProposal $proposal
     * @return array
     */
    public function offerValidationAction(Request $request, OrderProposal $proposal)
    {
        if ($request->getMethod() == "POST") {
            if ($request->request->has('accept')) {
                $status = $this->getDoctrine()->getRepository("OrderBundle:Status")->findOneBy(['id' => '2']);
                $this->get('mailer_sender')->sendOrderProposalToBuyerAccepted($proposal);
            }else {
                $status = $this->getDoctrine()->getRepository("OrderBundle:Status")->findOneBy(['id' => '3']);
                $this->get('mailer_sender')->sendOrderProposalToBuyerRefused($proposal);
            }

            $proposal->setStatus($status);
            $this->getDoctrine()->getManager()->persist($proposal);
            $this->getDoctrine()->getManager()->flush();
            $this->get('order_service')->changeOrderProposal($proposal);

            $status = $this->getDoctrine()->getRepository("OrderBundle:Status")->findOneBy(['id' => '3']);
            $proposals = $this->getDoctrine()->getRepository("OrderBundle:OrderProposal")
                ->findByProposalPending($proposal);
            /** @var OrderProposal $proposalP */
            foreach ($proposals as $proposalP) {
                if ($proposal != $proposalP) {
                    $proposalP->setStatus($status);
                    $this->get('mailer_sender')->sendOrderProposalToBuyerRefused($proposalP);
                    $this->getDoctrine()->getManager()->persist($proposalP);
                }
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('offer_validation', ['id' => $proposal->getId()]);
        }

        $total = $this->getDoctrine()->getRepository("OrderBundle:OrderProposal")->countByProposalPending($proposal);

        return ['proposal' => $proposal, 'product' => $proposal->getProduct(), 'total' => $total];
    }

}
