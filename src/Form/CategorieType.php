<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Enum\TypeCategorie; 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['placeholder' => 'Ex: Alimentation, Salaire...']
            ])
            
            // On utilise EnumType pour que Symfony comprenne ton Enum automatiquement
            ->add('type', EnumType::class, [
                'class' => TypeCategorie::class,
                'placeholder' => 'Choisir un type...', // Ton champ vide initial
                'choice_label' => function (TypeCategorie $choice) {
                    return ucfirst(strtolower($choice->name)); 
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}