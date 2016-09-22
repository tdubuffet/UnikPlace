<?php

namespace UserBundle\Form;

use FOS\UserBundle\Form\Type\ProfileFormType as AbstractProfileFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserAdminFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username'); // we use email as the username
        $builder->add('firstname');
        $builder->add('lastname');
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
        $builder->add('company_address');
        $builder->add('company_zipcode');
        $builder->add('company_city');
        $builder->add('enabled');
        $builder->add('email_validated', CheckboxType::class, ['required' => false]);
        $builder->add('locked');
        $builder->add('roles', ChoiceType::class, [
            'multiple'=> true,
            'expanded'=> true,
            'choices' => ['ROLE_USER' => 'ROLE_USER', 'ROLE_ADMIN' => 'ROLE_ADMIN']
            ]);
    }

    public function getName()
    {
        return 'app_form_profil';
    }
}