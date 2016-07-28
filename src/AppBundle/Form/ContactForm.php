<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 28/07/16
 * Time: 10:55
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                "subject",
                ChoiceType::class,
                [
                    'label' => "Sujet du message",
                    'label_attr' => ['class' => 'hidden'],
                    'choices' => [
                        'Renseignements' => 'information',
                        'Problème' => 'problem',
                        'Signaler un abus' => 'abuse',
                    ],
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nom',
                    'label_attr' => ['class' => 'hidden'],
                    'attr' => ['placeholder' => 'Votre nom'],
                    'required' => true,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'label_attr' => ['class' => 'hidden'],
                    'attr' => ['placeholder' => 'Votre adresse email'],
                    'required' => true,
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => 'Message',
                    'label_attr' => ['class' => 'hidden'],
                    'attr' => ['placeholder' => 'Votre message', 'rows' => 4],
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Envoyer']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('allow_extra_fields' => true));
    }

}