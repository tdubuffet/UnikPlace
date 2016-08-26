<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 25/08/16
 * Time: 17:00
 */

namespace Admin2Bundle\Form;

use Doctrine\ORM\EntityRepository;
use ProductBundle\Entity\Category;
use ProductBundle\Entity\Collection;
use ProductBundle\Form\CollectionImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, ['label' => "Nom", 'required' => true])
            ->add("description", TextareaType::class, ['label' => "Description", 'required' => true])
            ->add(
                "categories",
                EntityType::class,
                [
                    'label' => 'Catégories',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'ProductBundle\Entity\Category',
                ]
            )->add(
                "image",
                CollectionImageType::class,
                ['label' => 'Image', 'required' => $options['img_req'], 'label_attr' => ['class' => 'hidden']]
            )->add('save', SubmitType::class, ['label' => 'Sauvegarder la tendance']);

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'ProductBundle\Entity\Collection', 'img_req' => true]);
    }

}