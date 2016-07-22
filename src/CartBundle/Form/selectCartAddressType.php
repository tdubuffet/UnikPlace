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
        $builder
            ->add('delivery_address', ChoiceType::class,
                      array(
                          'label' => 'Adresse de livraison',
                          'choices' => $options['addresses'],
                          'choice_label' => function ($value, $key, $index) {
                              return $value->getName().' - '.$value->getStreet().' '.$value->getCity()->getZipcode().' '.$value->getCity()->getName();
                          },
                      )
        )->add('billing_address', ChoiceType::class,
               array(
                   'label' => 'Adresse de facturation',
                   'choices' => $options['addresses'],
                   'choice_label' => function ($value, $key, $index) {
                       return $value->getName().' - '.$value->getStreet().' '.$value->getCity()->getZipcode().' '.$value->getCity()->getName();
                   },
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
