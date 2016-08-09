<?php

namespace CartBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class selectCartAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach ($options['addresses'] as $address) {
            $choices[$address->getName().' - '.$address->getStreet().' '.$address->getCity()->getZipcode().' '.$address->getCity()->getName()] = $address->getId();
        }

        $builder
            ->add('delivery_address', ChoiceType::class,
                      array(
                          'label' => 'Adresse de livraison',
                          'choices' => $choices,
                      )
        )->add('billing_address', ChoiceType::class,
               array(
                   'label' => 'Adresse de facturation',
                   'choices' => $choices,
               )
        )->add('save', SubmitType::class, ['label' => 'Enregistrer et passer à l\'étape suivante']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'addresses' => null
        ));
    }
}
