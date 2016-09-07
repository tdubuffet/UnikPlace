<?php

namespace ProductBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AttributeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('attribute_type', EntityType::class, ['class' => 'ProductBundle:AttributeType', 'label' => 'Type d\'attribut'])
            ->add('referential', EntityType::class, ['class' => 'ProductBundle:Referential', 'label' => 'Réferentiel associé', 'required'   => false])
            ->add('attribute_deposit_template', EntityType::class, ['class' => 'ProductBundle:AttributeDepositTemplate', 'label' => 'Template au dépôt'])
            ->add('attribute_search_template', EntityType::class, ['class' => 'ProductBundle:AttributeSearchTemplate', 'label' => 'Template à la recherche'])
            ->add('mandatory', null, ['label' => 'Obligatoire au dépôt ?'])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ProductBundle\Entity\Attribute'
        ));
    }
}
