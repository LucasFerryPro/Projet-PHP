<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    #[Route('/reset', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur actuel, vous pouvez ajuster cette logique selon vos besoins

        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        // Créer le formulaire pour changer le mot change_password.html.twig passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le nouveau mot change_password.html.twig passe
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            // Définir le nouveau mot change_password.html.twig passe hashé pour l'utilisateur
            $user->setPassword($encodedPassword);

            // Enregistrer les modifications dans la base change_password.html.twig données
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            // Rediriger vers la page d'accueil ou toute autre page appropriée après la réinitialisation du mot change_password.html.twig passe
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/change_password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
