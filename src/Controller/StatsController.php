<?php
namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/stats')]
#[IsGranted('ROLE_USER')]
class StatsController extends AbstractController
{
    #[Route('/annuel', name: 'app_stats_annuel')]
    public function bilanAnnuel(TransactionRepository $transactionRepository): Response
    {
        $user = $this->getUser();
        $currentYear = (int) date('Y');

        // 1. On cherche par 'utilisateur' (le nom de ton champ en BDD)
        $transactions = $transactionRepository->findBy(['utilisateur' => $user]);

        // 2. Initialiser les tableaux de données (12 mois)
        $revenusParMois = array_fill(1, 12, 0);
        $depensesParMois = array_fill(1, 12, 0);
        $depensesParCategorie = [];

        foreach ($transactions as $t) {
            // 3. Utilisation de ton vrai getter de date : getDateTransaction()
            $date = $t->getDateTransaction(); 
            if ($date && (int)$date->format('Y') === $currentYear) {
                $mois = (int)$date->format('n');
                $montant = $t->getMontant();
                
                // Si le montant est positif, c'est un revenu, sinon une dépense
                if ($montant > 0) { 
                    $revenusParMois[$mois] += $montant;
                } else {
                    $depensesParMois[$mois] += abs($montant); // abs() pour stocker du positif dans le graphique
                    
                    // Répartition par catégorie
                    $catNom = $t->getCategorie() ? $t->getCategorie()->getNom() : 'Inconnue';
                    if (!isset($depensesParCategorie[$catNom])) {
                        $depensesParCategorie[$catNom] = 0;
                    }
                    $depensesParCategorie[$catNom] += abs($montant);
                }
            }
        }

        return $this->render('stats/annuel.html.twig', [
            'annee' => $currentYear,
            'revenus' => array_values($revenusParMois),
            'depenses' => array_values($depensesParMois),
            'categoriesLabels' => array_keys($depensesParCategorie),
            'categoriesData' => array_values($depensesParCategorie),
            'totalRevenus' => array_sum($revenusParMois),
            'totalDepenses' => array_sum($depensesParMois),
        ]);
    }
}