<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'notifications_history')]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function history(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $notifications = $em->getRepository(Notification::class)
            ->findBy(['recipient' => $user], ['CreatedAt' => 'DESC']);

        return $this->render('history/index.html.twig', [
            'user'          => $user,
            'notifications' => $notifications,
            'role'          => $user->getRoles()[0] ?? null,  
            'departement'   => $user->getDepartment(),        
        ]);
    }
}