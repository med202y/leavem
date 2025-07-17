<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\Departement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class dashboardcontroller extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->redirectToRoute('custom_login');
        }

        // Pass role and departement enums to Twig
        return $this->render('dashboard/dashboard.html.twig', [
            'user' => $user,
            'role' => $user->getRole()[0],
            'departement' => $user->getDepartement(),
        ]);
    }

}
