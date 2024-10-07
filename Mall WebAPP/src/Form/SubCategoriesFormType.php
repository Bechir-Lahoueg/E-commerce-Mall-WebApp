<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\SubCategories;
use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class SubCategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add ('categories',  EntityType::class,[
            'class' => Categories::class,
            'choice_label' => 'name', 
        ])
            ->add('name')
           
            ->add('photo', FileType::class,[
            'required' => false,
            'mapped' => false,
          // set to false to allow empty file submission
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubCategories::class,
        ]);
    }
}
