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
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Http\Attribute\IsGranted; // pour éviter une faille de sécurité ou un crash (le fameux « Call to a member function... on null ») si jamais quelqu'un tentait de forcer l'URL, on ajoute l'attribut de sécurité au-dessus de la classe de contrôleur.

#[Route('/profil')]
class ProfilController extends AbstractController
{
    #[Route('/informations', name: 'app_profil_infos', methods: ['GET', 'POST'])]
    public function infos(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // 🛡️ Le FormBuilder intègre CSRF par défaut, mais on peut le configurer explicitement
        $form = $this->createFormBuilder($user, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'profile_infos_item',
        ])
        ->add('email', EmailType::class, [
            'label' => 'Adresse Email',
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
        
        // 🛡️ Sécurisation explicite du formulaire de mot de passe contre les failles CSRF
        $form = $this->createFormBuilder(null, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'profile_password_item',
        ])
        ->add('oldPassword', PasswordType::class, [
            'label' => 'Mot de passe actuel',
        ])
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Les deux mots de passe doivent correspondre.',
            'first_options'  => ['label' => 'Nouveau mot de passe'],
            'second_options' => ['label' => 'Répéter le nouveau mot de passe'],
        ])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            if (!$passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
                $form->get('oldPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
            } else {
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