<?php

namespace UserBundle\Form;

use LocationBundle\Form\AddressProType;
use LocationBundle\Form\AddressType;
use Symfony\Component\Form\AbstractType;
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
        $builder->add('phone');
        $builder->add('company_code');
        $builder->add('company_name');

        $builder->add('address', AddressProType::class, [
            'mapped' => false
        ]);
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