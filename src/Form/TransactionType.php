<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\MoyenPaiement;
use App\Entity\Transaction;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant')
            ->add('dateTransaction', null, [
                'widget' => 'single_text'
            ])
            ->add('libelleTransaction')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom', // <-- Indique à Symfony d'afficher la propriété "nom" (ou "libelle" selon ton entité)
                'placeholder' => 'Choisir une catégorie...', // Un champ vide propre au départ
                'required' => true,
            ])
            ->add('moyenPaiement', EntityType::class, [
                'class' => MoyenPaiement::class,
                'choice_label' => 'nom', // <-- Remplace par la propriété de ton entité (ex: 'nom', 'libelle')
                'placeholder' => 'Choisir un mode de règlement',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
