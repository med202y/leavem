<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Entity\User;
use App\Enum\StatutDeDemande;
use App\Repository\CongeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

class teamleavescontroller extends AbstractController
{
    #[Route('/manager_leaves', name: 'manager_team_leaves')]
    public function showTeamLeaves(
        SessionInterface $session,
        EntityManagerInterface $em,
        CongeRepository $congeRepository
    ): Response
    {
        // Récupérer l'utilisateur connecté
        $userId = $session->get('user_id');

        // Vérifier qu'il est connecté
        if (!$userId) {
            return $this->redirectToRoute('custom_login'); // Ou une autre route de login
        }
        $user = $em->getRepository(User::class)->find($userId);
        // Vérifier le rôle "ROLE_MANAGER"
        if (!in_array('ROLE_MANAGER', $user->getRoles(), true)) {
            throw new AccessDeniedException('Vous devez être manager pour accéder à cette page.');
        }

        // Récupérer son équipe
        $team = $user->getTeam();
        if (!$team) {
            throw new \LogicException('L\'utilisateur manager n\'a pas d\'équipe assignée.');
        }

        // Requête pour récupérer les congés de son équipe
        $conges = $congeRepository->createQueryBuilder('c')
            ->join('c.user', 'u')
            ->where('u.team = :team')
            ->setParameter('team', $team)
            ->orderBy('c.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
        $cases = StatutDeDemande::cases();
        $statuses = array_map(fn($c) => $c->value, $cases);

        // 2) Map each statut to a Tailwind color (choose your shades)
        $statusColors = [
            StatutDeDemande::EN_ATTENTE->value  => 'yellow-300',
            StatutDeDemande::ACCEPTE->value   => 'green-300',
            StatutDeDemande::REFUSE->value    => 'red-300',
            StatutDeDemande::ANNULEE->value    => 'gray-300',
        ];

        return $this->render('team_leaves.html.twig', [
            'conges' => $conges,
            'statuses'     => $statuses,
            'statusColors' => $statusColors,
        ]);
    }

    #[Route('/manager_leaves/update/{id}', name: 'manager_conge_update', methods: ['POST'])]
    public function updateCongeStatus(
        Request $request,
        Conge $conge,
        EntityManagerInterface $em,
        SessionInterface $session
    ): JsonResponse {
        // 1) Session‑based login check (same as your list action)
        $userId = $session->get('user_id');
        if (!$userId) {
            return new JsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
        }

        $manager = $em->getRepository(User::class)->find($userId);
        if (!in_array('ROLE_MANAGER', $manager->getRoles(), true)) {
            return new JsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        // 2) Validate action
        $action = $request->request->get('action');
        if (!in_array($action, ['accept', 'refuse'], true)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }

        // 3) Apply the change
        $newStatut = $action === 'accept'
            ? StatutDeDemande::ACCEPTE
            : StatutDeDemande::REFUSE;

        $conge
            ->setStatut($newStatut)
            ->setDateTraitement(new \DateTime())
            ->setTraitePar($manager->getPrenom() . ' ' . $manager->getNom());

        // 4) Flush
        $em->flush();

        // 5) Return success so your JS can reload/replace the card
        return new JsonResponse(['success' => true]);
    }
}


