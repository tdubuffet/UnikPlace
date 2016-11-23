<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 25/08/16
 * Time: 11:52
 */

namespace Admin2Bundle\Form;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Doctrine\ORM\EntityRepository;
use ProductBundle\Entity\Category;
use ProductBundle\Form\CategoryImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCategoryForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("translations", TranslationsType::class, [
                'exclude_fields' => ['slug']
            ])
            ->add(
                "parent",
                EntityType::class,
                [
                    'label' => 'Parent',
                    'required' => false,
                    'class' => 'ProductBundle\Entity\Category',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->where('c.parent IS NULL')
                            ->leftJoin("c.children", "categories")
                            ->groupBy("c.id")
                            ->having("COUNT(categories.id) > 0");
                    },
                ]
            )
            ->add(
                "children",
                EntityType::class,
                [
                    'label' => 'Enfants',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'ProductBundle\Entity\Category'
                ]
            )
            ->add(
                "attributes",
                EntityType::class,
                [
                    'label' => 'Attributs',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'ProductBundle\Entity\Attribute'
                ])
            ->add(
                "collections",
                EntityType::class,
                [
                    'label' => 'Collections',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'ProductBundle\Entity\Collection'
                ])
            ->add(
                "image",
                CategoryImageType::class,
                ['label' => 'Image', 'required' => $options['img_req'], 'label_attr' => ['class' => 'hidden']]
            )

            ->add(
                "emcCode",
                ChoiceType::class,
                [
                    'label' => 'Catégorie envoi moins cher',
                    'required' => true,
                    'choices' => $options['emcCategories']
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Sauvegarder la catégorie']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'ProductBundle\Entity\Category', 'img_req' => true, 'emcCategories' => []]);
    }

}