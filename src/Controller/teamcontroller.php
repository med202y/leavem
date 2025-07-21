<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class teamcontroller extends AbstractController
{
    #[Route('/team', name: 'team')]
    public function index(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        // 1) Vérifier que l'utilisateur est connecté
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        // 2) Récupérer l'utilisateur
        /** @var User|null $user */
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('custom_login');
        }

        // 3) Récupérer son équipe
        $team = $user->getTeam();

        // 4) S'il y a bien une équipe, récupérer tous les utilisateurs qui y appartiennent
        $members = [];
        if ($team instanceof Team) {
            $members = $em
                ->getRepository(User::class)
                ->findBy(['team' => $team], ['nom' => 'ASC']);
        }

        // 5) Renvoyer la vue
        return $this->render('team.html.twig', [
            'user'      => $user,
            'team'      => $team,
            'members'   => $members,
            'role'      => $user->getRole()[0] ?? null,
            'departement' => $user->getDepartement(),
        ]);
    }
}
