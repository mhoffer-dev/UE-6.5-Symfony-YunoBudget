<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\MoyenPaiement;
use App\Entity\Transaction;
use App\Repository\CategorieRepository;
use App\Repository\MoyenPaiementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // 1. On récupère l'utilisateur connecté qu'on va envoyer depuis le contrôleur
        $user = $options['user'];

        $builder
            ->add('montant')
            ->add('libelleTransaction')
            ->add('dateTransaction')
            
            // 2. On filtre la liste des catégories pour n'afficher que les siennes
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'query_builder' => function (CategorieRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.utilisateur = :user')
                        ->setParameter('user', $user);
                },
            ])
            
            // 3. On filtre la liste des moyens de paiement pour n'afficher que les siens
            ->add('moyenPaiement', EntityType::class, [
                'class' => MoyenPaiement::class,
                'choice_label' => 'nom',
                'query_builder' => function (MoyenPaiementRepository $er) use ($user) {
                    return $er->createQueryBuilder('m')
                        ->where('m.utilisateur = :user')
                        ->setParameter('user', $user);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
        
        // 4. On indique à Symfony que l'option 'user' est désormais obligatoire
        $resolver->setRequired('user');
    }
}