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
        $user = $this->getUser();

        $team = $user->getTeam();
        $members = [];
        if ($team instanceof Team) {
            $members = $em->getRepository(User::class)
                ->findBy(['team' => $team], ['nom' => 'ASC']);
        }

        return $this->render('team/index.html.twig', [
            'user'      => $user,
            'team'      => $team,
            'members'   => $members,
            'role'      => $user->getRole()[0] ?? null,
            'departement' => $user->getDepartement(),
        ]);
    }
}
