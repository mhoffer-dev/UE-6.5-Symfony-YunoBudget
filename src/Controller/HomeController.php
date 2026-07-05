<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant la page d'accueil de l'application Yuno Budget.
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TransactionRepository $transactionRepository): Response
    {
        $user = $this->getUser();
        
        $totalRevenus = 0.0;
        $totalDepenses = 0.0;
        $recentTransactions = [];

        // Si l'utilisateur est connecté, on calcule ses indicateurs financiers
        if ($user) {
            $transactions = $transactionRepository->findBy(
                ['utilisateur' => $user],
                ['dateTransaction' => 'DESC']
            );

            foreach ($transactions as $t) {
                $montant = $t->getMontant();
                if ($montant > 0) {
                    $totalRevenus += $montant;
                } else {
                    $totalDepenses += abs($montant);
                }
            }

            // On garde les 5 transactions les plus récentes pour le widget d'accueil
            $recentTransactions = array_slice($transactions, 0, 5);

            // Nettoyage des Proxies pour éviter les erreurs de référence vide
            foreach ($recentTransactions as $transaction) {
                try {
                    if ($transaction->getMoyenPaiement() !== null) {
                        $transaction->getMoyenPaiement()->getNom();
                    }
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $transaction->setMoyenPaiement(null);
                }
            }
        }

        // Affichage du template de la page d'accueil
        return $this->render('home/index.html.twig', [
            'titre' => 'Bienvenue sur Yuno Budget',
            'totalRevenus' => $totalRevenus,
            'totalDepenses' => $totalDepenses,
            'solde' => $totalRevenus - $totalDepenses,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}