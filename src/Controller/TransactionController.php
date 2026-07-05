<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\CategorieRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transaction')]
#[IsGranted('ROLE_USER')]
final class TransactionController extends AbstractController
{
    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(Request $request, TransactionRepository $transactionRepository, CategorieRepository $categorieRepository): Response
    {
        $user = $this->getUser();

        // 1. Récupération des paramètres de filtrage depuis l'URL (recherche, catégorie, type)
        $search = $request->query->get('q');
        $categorieId = $request->query->get('categorie') ? (int) $request->query->get('categorie') : null;
        $type = $request->query->get('type');

        // 2. Recherche filtrée en base de données (isolée par utilisateur)
        $transactions = $transactionRepository->findWithFilters($user, $search, $categorieId, $type);

        // 3. Récupération des catégories de l'utilisateur pour alimenter le select de filtre
        $categories = $categorieRepository->findBy(['utilisateur' => $user], ['nom' => 'ASC']);

        // 4. Nettoyage des Proxies cassés (ID 0 ou fantômes) pour éviter le crash de Doctrine
        foreach ($transactions as $transaction) {
            try {
                if ($transaction->getMoyenPaiement() !== null) {
                    $transaction->getMoyenPaiement()->getNom(); 
                }
            } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                $transaction->setMoyenPaiement(null);
            }
        }

        // 5. Envoi des données et des filtres actifs au template Twig
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
            'categories' => $categories,
            'active_search' => $search,
            'active_categorie' => $categorieId,
            'active_type' => $type,
        ]);
    }

    #[Route('/new', name: 'app_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transaction = new Transaction();
        
        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setUtilisateur($this->getUser());
            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Transaction ajoutée avec succès.');
            return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/export/csv', name: 'app_transaction_export_csv', methods: ['GET'])]
    public function exportCsv(Request $request, TransactionRepository $transactionRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Accès refusé.");
        }

        // Récupération des filtres actifs pour exporter uniquement ce que l'utilisateur voit
        $search = $request->query->get('q');
        $categorieId = $request->query->get('categorie') ? (int) $request->query->get('categorie') : null;
        $type = $request->query->get('type');

        $transactions = $transactionRepository->findWithFilters($user, $search, $categorieId, $type);

        // Ajout du BOM UTF-8 pour une compatibilité parfaite avec Microsoft Excel (accents)
        $csvContent = "\xEF\xBB\xBF";
        $csvContent .= "ID;Date;Description;Catégorie;Type;Moyen de Paiement;Montant (€)\n";

        foreach ($transactions as $t) {
            $id = $t->getId();
            $date = $t->getDateTransaction() ? $t->getDateTransaction()->format('d/m/Y H:i') : '';
            
            // Échappement anti-injection CSV et guillemets
            $libelle = '"' . str_replace('"', '""', (string)$t->getLibelleTransaction()) . '"';
            $categorie = $t->getCategorie() ? '"' . str_replace('"', '""', $t->getCategorie()->getNom()) . '"' : 'Sans catégorie';
            $typeFlux = $t->getMontant() >= 0 ? 'Revenu' : 'Dépense';
            
            $moyen = 'Non spécifié';
            try {
                if ($t->getMoyenPaiement() !== null) {
                    $moyen = '"' . str_replace('"', '""', $t->getMoyenPaiement()->getNom()) . '"';
                }
            } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                // Ignore proxy fantôme
            }

            $montant = number_format($t->getMontant(), 2, ',', ' ');

            $csvContent .= sprintf("%s;%s;%s;%s;%s;%s;%s\n", $id, $date, $libelle, $categorie, $typeFlux, $moyen, $montant);
        }

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="transactions_yunobudget_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/{id}', name: 'app_transaction_show', methods: ['GET'])]
    public function show(Transaction $transaction): Response
    {
        // 🛡️ Protection Cybersécurité IDOR : Vérification stricte du propriétaire
        if ($transaction->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à consulter cette transaction.");
        }

        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        // 🛡️ Protection Cybersécurité IDOR : Vérification stricte du propriétaire
        if ($transaction->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cette transaction.");
        }

        try {
            if ($transaction->getMoyenPaiement() !== null) {
                $transaction->getMoyenPaiement()->getNom();
            }
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            $transaction->setMoyenPaiement(null);
        }

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Transaction modifiée avec succès.');
            return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        // 🛡️ Protection Cybersécurité IDOR : Vérification stricte du propriétaire
        if ($transaction->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à supprimer cette transaction.");
        }

        $token = $request->getPayload()->getString('_token');

        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $token)) {
            $entityManager->remove($transaction);
            $entityManager->flush();
            $this->addFlash('success', 'Transaction supprimée avec succès.');
        }

        return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
    }
}
