<?php

namespace UserBundle\Form;

use FOS\UserBundle\Form\Type\ProfileFormType as AbstractProfileFormType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends AbstractProfileFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username'); // we use email as the username
        $builder->add('firstname');
        $builder->add('lastname');
    }

    public function getName()
    {
        return 'app_form_profil';
    }
}