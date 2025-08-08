<?php

namespace App\Controller\Team;

use App\Entity\User;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

        $team = $user->getTeam();
        $members = [];
        
        if ($team instanceof Team) {
            // Fixed: Changed 'nom' to 'lastName' to match User entity property
            $members = $em->getRepository(User::class)
                ->findBy(['team' => $team], ['lastName' => 'ASC']);
        }

        // Get user roles - fixed method call
        $userRoles = $user->getRoles(); // This returns string array
        $firstRole = !empty($userRoles) ? $userRoles[0] : null;

        return $this->render('team/index.html.twig', [
            'user'       => $user,
            'team'       => $team,
            'members'    => $members,
            'role'       => $firstRole,
            'departement' => $user->getDepartment(), // Fixed: getDepartment() not getDepartement()
        ]);
    }
}