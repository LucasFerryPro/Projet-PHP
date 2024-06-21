<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{

     #[Route('/profile/edit', name: 'edit_profile')]
    public function edit(Request $request,  EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Crée un formulaire basé sur ProfileType et l'entité User
        $form = $this->createForm(ProfileType::class, $user);

        // Gère la soumission du formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistre les modifications dans la base de données
            $entityManager->flush();

            // Redirige vers une page de confirmation ou une autre action
            return $this->redirectToRoute('home');
        }

        // Rend le formulaire dans le template Twig
        return $this->render('profile/edit.html.twig', [
            'editProfileForm' => $form->createView(),
        ]);
    }
}