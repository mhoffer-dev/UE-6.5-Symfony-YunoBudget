<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant la page d'accueil de l'application Yuno Budget.
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Affichage du template de la page d'accueil
        return $this->render('home/index.html.twig', [
            'titre' => 'Bienvenue sur Yuno Budget',
        ]);
    }
}