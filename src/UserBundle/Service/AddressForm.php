<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 17/10/2016
 * Time: 14:09
 */

namespace UserBundle\Service;


use Doctrine\ORM\EntityManager;
use LocationBundle\Entity\Address;
use LocationBundle\Entity\City;
use LocationBundle\Form\AddressType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class AddressForm
{
    
    public function __construct(FormFactory $formFactory, EntityManager $em)
    {
        
        $this->formFactory = $formFactory;
        $this->em = $em;
        
    }
    
    public function getForm(Request $request, User $user, $flashMessage = false)
    {

        $addAddressForm = $this->formFactory->create(AddressType::class, null);
        $addAddressForm->handleRequest($request);

        if ($addAddressForm->isValid() && $addAddressForm->isSubmitted()) {

            $values = $addAddressForm->getData();


            $cityName = $values['locality'];
            $cityZipCode = $values['postal_code'];

            $city = $this->em
                ->getRepository('LocationBundle:City')
                ->findOneBy(['name' => $cityName, 'zipcode' => $cityZipCode]);

            if (!$city) {

                $county = $this->em->getRepository('LocationBundle:County')->findOneBy(['code' =>  substr($cityZipCode, 0, 2)]);
                if (!$county) {
                    throw new Exception('County not found');
                }

                $city = new City();
                $city->setCounty($county);
                $city->setName($cityName);
                $city->setZipcode($cityZipCode);

                $this->em->persist($city);
                $this->em->flush();
            }


            $address = new Address();
            $address->setCity($city)
                ->setUser($user);

            $address->setFirstname($values['firstname']);
            $address->setLastname($values['lastname']);

            $address->setStreet($values['street_number'] . ' ' . $values['route']);
            $address->setAdditional($values['additional']);

            $this->em->persist($address);
            $this->em->flush();
            
            return true;
        }

        return $addAddressForm;
        
    }
    

}