<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class historycontroller extends AbstractController
{
    #[Route('/history', name: 'notifications_history')]
    public function history(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        // Vérifie que l'utilisateur est connecté
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        // Récupère l'entité User
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('custom_login');
        }

        // Récupère toutes les notifications pour cet utilisateur, les plus récentes en premier
        $notifications = $em->getRepository(Notification::class)
            ->findBy(
                ['recipient' => $user],
                ['CreatedAt' => 'DESC']
            );

        // Rend la vue history.html.twig avec les données nécessaires
        return $this->render('history.html.twig', [
            'user'          => $user,
            'notifications' => $notifications,
            'role'          => $user->getRole()[0] ?? null,
            'departement'   => $user->getDepartement(),
        ]);
    }
}
