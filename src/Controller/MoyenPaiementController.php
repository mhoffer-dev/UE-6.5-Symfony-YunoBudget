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

#[Route('/moyen/paiement')]
final class MoyenPaiementController extends AbstractController
{
    #[Route(name: 'app_moyen_paiement_index', methods: ['GET'])]
    public function index(MoyenPaiementRepository $moyenPaiementRepository): Response
    {
        return $this->render('moyen_paiement/index.html.twig', [
            'moyen_paiements' => $moyenPaiementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_moyen_paiement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $moyenPaiement = new MoyenPaiement();
        $form = $this->createForm(MoyenPaiementType::class, $moyenPaiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($moyenPaiement);
            $entityManager->flush();

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
        return $this->render('moyen_paiement/show.html.twig', [
            'moyen_paiement' => $moyenPaiement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_moyen_paiement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoyenPaiement $moyenPaiement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MoyenPaiementType::class, $moyenPaiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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
        if ($this->isCsrfTokenValid('delete'.$moyenPaiement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($moyenPaiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_moyen_paiement_index', [], Response::HTTP_SEE_OTHER);
    }
}
