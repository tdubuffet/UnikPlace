<?php

namespace UserBundle\Form;

use Doctrine\DBAL\Types\TextType;
use LocationBundle\Form\AddressProType;
use LocationBundle\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username'); // we use email as the username
        $builder->add('firstname');
        $builder->add('lastname');
        $builder->add('pro', ChoiceType::class, array(
            'choices'  => array(
                'Particulier' => false,
                'Professionnel' => true,
            ),
            'expanded' => true,
            'multiple' => false,
        ));
        $builder->add('phone');
        $builder->add('birthday', DateType::class, array(
            'format'      => \IntlDateFormatter::LONG,
            'years' => range(date('Y'), date('Y')-90)
        ));
        $builder->add('nationality', CountryType::class, array(
            'preferred_choices' => array('FR'),
        ));
        $builder->add('residential_country', CountryType::class, array(
            'preferred_choices' => array('FR'),
        ));
        $builder->add('company_code');
        $builder->add('company_name');
        $builder->add('civility', ChoiceType::class, [
            'label' => 'Civilité',
            'choices' => [
                'Monsieur' => 'mr',
                'Madame' => 'mrs'
            ],
            'required' => true,
            'mapped' => false
        ]);

        $builder->add('street', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Adresse de l\'entreprise',
            'required' => true,
            'mapped' => false,
            'attr' => [
            ]
        ])
            ->add('street_number', HiddenType::class ,['mapped' => false])
            ->add('locality', HiddenType::class ,['mapped' => false])
            ->add('route', HiddenType::class ,['mapped' => false])
            ->add('administrative_area_level_1', HiddenType::class ,['mapped' => false])
            ->add('postal_code', HiddenType::class ,['mapped' => false])
            ->add('country', HiddenType::class ,['mapped' => false]);
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

}