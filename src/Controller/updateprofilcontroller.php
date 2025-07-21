<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class updateprofilcontroller extends AbstractController
{
    #[Route('/user_profile', name: 'user_profile')]
    public function user_profile(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Récupération de l'ID utilisateur depuis la session
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        // Chargement de l'utilisateur
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->redirectToRoute('custom_login');
        }

        // Création et gestion du formulaire
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du changement de mot de passe
            $oldPassword = $request->request->get('old_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            // Si l'utilisateur veut changer son mot de passe
            if (!empty($newPassword)) {
                // Vérifier l'ancien mot de passe
                if (empty($oldPassword) || !$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $this->addFlash('error', 'L\'ancien mot de passe est incorrect');
                    return $this->render('updateprofil.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Vérifier que les nouveaux mots de passe correspondent
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas');
                    return $this->render('updateprofil.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Vérifier la longueur du mot de passe
                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères');
                    return $this->render('updateprofil.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Hasher et sauvegarder le nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setMotDePasse($hashedPassword);
            }
            $notification = new Notification();
            $notification->setTitle('Profil mis a jour');
            $notification->setMessage("Votre mot de passe a été changé avec succes");
            $notification->setRecipient($user);

            $em->persist($notification);
            $em->flush();

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('updateprofil.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
