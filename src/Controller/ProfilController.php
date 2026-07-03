<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;

#[Route('/profil')]
class ProfilController extends AbstractController
{
    #[Route('/informations', name: 'app_profil_infos', methods: ['GET', 'POST'])]
    public function infos(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Formulaire de modification d'email en ligne
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Vos informations ont bien été mises à jour.');
            return $this->redirectToRoute('app_profil_infos');
        }

        return $this->render('profil/infos.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mot-de-passe', name: 'app_profil_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add('oldPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'attr' => ['class' => 'form-control']
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'first_options'  => ['label' => 'Nouveau mot de passe', 'attr' => ['class' => 'form-control']],
                'second_options' => ['label' => 'Répéter le nouveau mot de passe', 'attr' => ['class' => 'form-control']],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Vérification que l'ancien mot de passe saisi est le bon
            if (!$passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
                $form->get('oldPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
            } else {
                // Hachage et sauvegarde du nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $data['plainPassword']);
                $user->setPassword($hashedPassword);
                
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été modifié !');
                return $this->redirectToRoute('app_profil_password');
            }
        }

        return $this->render('profil/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}