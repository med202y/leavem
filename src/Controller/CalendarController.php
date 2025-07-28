<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Enum\statutdedemande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(EntityManagerInterface $em): Response
    {
        $conges = $em->getRepository(Conge::class)->findBy([
            'statut' => statutdedemande::ACCEPTE
        ]);

        $events = [];
        foreach ($conges as $conge) {
            $user = $conge->getUser();
            $events[] = [
                'title' => $user->getPrenom() . ' ' . $user->getNom(),
                'start' => $conge->getDateDebut()->format('Y-m-d'),
                'end'   => $conge->getDateFin()->modify('+1 day')->format('Y-m-d'),
                'color' => '#008080',
            ];
        }

        return $this->render('calendar.html.twig', [
            'events' => json_encode($events),
        ]);
    }
}
