<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class TransactionController extends AbstractController
{
    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepository): Response
    {
        // 1. On récupère les transactions de l'utilisateur
        $transactions = $transactionRepository->findBy(['utilisateur' => $this->getUser()]);

        // 2. On nettoie les Proxies cassés (ID 0 ou fantômes) pour éviter le crash de Doctrine
        foreach ($transactions as $transaction) {
            try {
                // On force Doctrine à charger le Moyen de Paiement
                if ($transaction->getMoyenPaiement() !== null) {
                    $transaction->getMoyenPaiement()->getNom(); 
                }
            } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                // 🔥 Si l'objet n'existe pas (ID 0 ou supprimé), on force à null en PHP !
                $transaction->setMoyenPaiement(null);
            }
        }

        // 3. On envoie les transactions nettoyées à Twig
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
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

            return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transaction_show', methods: ['GET'])]
    public function show(Transaction $transaction): Response
    {
        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($transaction->getMoyenPaiement() !== null) {
                $transaction->getMoyenPaiement()->getNom(); // Force le chargement pour déclencher l'erreur si besoin
            }
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            $transaction->setMoyenPaiement(null);
        }

        // On crée le formulaire en passant l'utilisateur connecté (comme on l'a configuré avant)
        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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
        $token = $request->getPayload()->getString('_token');

        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $token)) {
            $entityManager->remove($transaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_transaction_index', [], Response::HTTP_SEE_OTHER);
    }
}
