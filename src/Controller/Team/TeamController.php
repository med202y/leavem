<?php

namespace App\Controller\Team;

use App\Entity\User;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TeamController extends AbstractController
{
    #[Route('/team', name: 'team')]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $userRoles = $user->getRoles(); 
        $firstRole = !empty($userRoles) ? $userRoles[0] : null;
        $isAdmin = in_array('ROLE_ADMIN', $userRoles);

        if ($isAdmin) {
            // For admin users: show all teams with their members
            $teams = $em->getRepository(Team::class)
                ->createQueryBuilder('t')
                ->leftJoin('t.manager', 'm')
                ->leftJoin('t.members', 'members')
                ->addSelect('m', 'members')
                ->orderBy('t.name', 'ASC')
                ->getQuery()
                ->getResult();

            return $this->render('team/admin_index.html.twig', [
                'user' => $user,
                'teams' => $teams,
                'role' => $firstRole,
                'departement' => $user->getDepartment(),
            ]);
        } else {
            // For regular users: show only their team
            $team = $user->getTeam();
            $members = [];
            
            if ($team instanceof Team) {
                $members = $em->getRepository(User::class)
                    ->findBy(['team' => $team], ['lastName' => 'ASC']);
            }

            return $this->render('team/index.html.twig', [
                'user' => $user,
                'team' => $team,
                'members' => $members,
                'role' => $firstRole,
                'departement' => $user->getDepartment(), 
            ]);
        }
    }

    #[Route('/team/{id}/members', name: 'team_get_members', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getTeamMembers(Team $team, EntityManagerInterface $em): JsonResponse
    {
        $members = $team->getMembers()->map(function($member) {
            return [
                'id' => $member->getId(),
                'firstName' => $member->getFirstName(),
                'lastName' => $member->getLastName(),
                'department' => $member->getDepartment()->value ?? 'Non assigné',
                'isManager' => $member === $team->getManager()
            ];
        })->toArray();

        return new JsonResponse([
            'teamId' => $team->getId(),
            'teamName' => $team->getName(),
            'members' => array_values($members)
        ]);
    }

   
}