<?php

namespace App\Controller;

use App\Entity\MoyenPaiement;
use App\Form\MoyenPaiementType;
use App\Repository\MoyenPaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/moyen/paiement')]
#[IsGranted('ROLE_USER')]
final class MoyenPaiementController extends AbstractController
{
    #[Route(name: 'app_moyen_paiement_index', methods: ['GET'])]
    public function index(MoyenPaiementRepository $moyenPaiementRepository): Response
    {
        return $this->render('moyen_paiement/index.html.twig', [
            'moyen_paiements' => $moyenPaiementRepository->findBy(['utilisateur' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_moyen_paiement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moyenPaiement = new MoyenPaiement();
        $form = $this->createForm(MoyenPaiementType::class, $moyenPaiement, [
            'csrf_token_id' => 'moyenpaiement_new',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $moyenPaiement->setUtilisateur($this->getUser());

            $entityManager->persist($moyenPaiement);
            $entityManager->flush();

            $this->addFlash('success', 'Moyen de paiement créé avec succès.');
            return $this->redirectToRoute('app_moyen_paiement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('moyen_paiement/new.html.twig', [
            'moyen_paiement' => $moyenPaiement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_moyen_paiement_show', methods: ['GET'])]
    public function show(MoyenPaiement $moyenPaiement): Response
    {
        // 🛡️ Protection Cybersécurité IDOR : Vérification stricte du propriétaire
        if ($moyenPaiement->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à consulter ce moyen de paiement.");
        }

        return $this->render('moyen_paiement/show.html.twig', [
            'moyen_paiement' => $moyenPaiement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_moyen_paiement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoyenPaiement $moyenPaiement, EntityManagerInterface $entityManager): Response
    {
        // 🛡️ Protection Cybersécurité IDOR : Vérification stricte du propriétaire
        if ($moyenPaiement->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier ce moyen de paiement.");
        }

        $form = $this->createForm(MoyenPaiementType::class, $moyenPaiement, [
            'csrf_token_id' => 'moyenpaiement_edit_' . $moyenPaiement->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($moyenPaiement->getUtilisateur() === null) {
                $moyenPaiement->setUtilisateur($this->getUser());
            }

            $entityManager->flush();

            $this->addFlash('success', 'Moyen de paiement modifié avec succès.');
            return $this->redirectToRoute('app_moyen_paiement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('moyen_paiement/edit.html.twig', [
            'moyen_paiement' => $moyenPaiement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_moyen_paiement_delete', methods: ['POST'])]
    public function delete(Request $request, MoyenPaiement $moyenPaiement, EntityManagerInterface $entityManager): Response
    {
        // 🛡️ Protection Cybersécurité IDOR : Vérification stricte du propriétaire
        if ($moyenPaiement->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à supprimer ce moyen de paiement.");
        }

        $token = $request->getPayload()->getString('_token');

        if ($this->isCsrfTokenValid('delete'.$moyenPaiement->getId(), $token)) {
            $entityManager->remove($moyenPaiement);
            $entityManager->flush();
            $this->addFlash('success', 'Moyen de paiement supprimé avec succès.');
        }

        return $this->redirectToRoute('app_moyen_paiement_index', [], Response::HTTP_SEE_OTHER);
    }
}