<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Form\UserProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
 #[IsGranted('ROLE_EMPLOYEE')]

class ProfileController extends AbstractController
{
    #[Route('/user_profile', name: 'user_profile')]
   
    public function user_profile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        NotificationRepository $notificationRepository,
        EntityManagerInterface $em,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Création et gestion du formulaire
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des valeurs depuis le formulaire
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            // Si l'utilisateur veut changer son mot de passe
            if (!empty($newPassword)) {
                // Vérifier l'ancien mot de passe
                if (empty($oldPassword) || !$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $this->addFlash('error', 'L\'ancien mot de passe est incorrect');
                    return $this->render('profile/edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Vérifier que les nouveaux mots de passe correspondent
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas');
                    return $this->render('profile/edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Vérifier la longueur du mot de passe
                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères');
                    return $this->render('profile/edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Hasher et sauvegarder le nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                 $em->persist($user);
                 $em->flush();
            }

            // Notifier de la mise à jour
            $notificationRepository->profileUpdateNotification($user);

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
