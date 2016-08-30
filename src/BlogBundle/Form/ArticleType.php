<?php

namespace BlogBundle\Form;

use KMS\FroalaEditorBundle\Form\Type\FroalaEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('category')
            ->add('content', FroalaEditorType::class)
            ->add('published', CheckboxType::class, ['label' => 'Publier l\'article ?', 'required' => false])
            ->add(
                "image",
                ArticleImageType::class,
                ['label' => 'Image', 'required' => $options['img_req'], 'label_attr' => ['class' => 'hidden']]
            )
            //->add('author')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BlogBundle\Entity\Article', 'img_req' => true
        ));
    }
}
