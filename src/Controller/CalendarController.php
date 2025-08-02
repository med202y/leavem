<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Enum\statutdedemande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer les statuts sélectionnés depuis les paramètres de requête
        $selectedStatuses = $request->query->all('statuses') ?: [statutdedemande::ACCEPTE->value];

        // Convertir les valeurs string en enum cases
        $statusEnums = [];
        foreach ($selectedStatuses as $statusValue) {
            foreach (statutdedemande::cases() as $case) {
                if ($case->value === $statusValue) {
                    $statusEnums[] = $case;
                    break;
                }
            }
        }

        // Requête pour récupérer les congés avec les statuts sélectionnés
        $qb = $em->getRepository(Conge::class)->createQueryBuilder('c');
        if (!empty($statusEnums)) {
            $qb->where($qb->expr()->in('c.statut', ':statuses'))
                ->setParameter('statuses', $statusEnums);
        }
        $conges = $qb->getQuery()->getResult();

        $events = $this->buildEvents($conges);

        // Préparer les données pour le template
        $allStatuses = [];
        foreach (statutdedemande::cases() as $status) {
            $allStatuses[] = [
                'value' => $status->value,
                'label' => $status->label(),
                'selected' => in_array($status->value, $selectedStatuses)
            ];
        }

        return $this->render('calendar\index.html.twig', [
            'events' => json_encode($events),
            'statuses' => $allStatuses,
            'selectedStatuses' => $selectedStatuses
        ]);
    }

    #[Route('/calendar/events', name: 'app_calendar_events', methods: ['GET'])]
    public function getEvents(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $selectedStatuses = $request->query->all('statuses') ?: [];

        // Convertir les valeurs string en enum cases
        $statusEnums = [];
        foreach ($selectedStatuses as $statusValue) {
            foreach (statutdedemande::cases() as $case) {
                if ($case->value === $statusValue) {
                    $statusEnums[] = $case;
                    break;
                }
            }
        }

        // Requête pour récupérer les congés avec les statuts sélectionnés
        $qb = $em->getRepository(Conge::class)->createQueryBuilder('c');
        if (!empty($statusEnums)) {
            $qb->where($qb->expr()->in('c.statut', ':statuses'))
                ->setParameter('statuses', $statusEnums);
        }
        $conges = $qb->getQuery()->getResult();

        $events = $this->buildEvents($conges);

        return new JsonResponse($events);
    }

    private function buildEvents(array $conges): array
    {
        $events = [];
        $statusColors = [
            statutdedemande::EN_ATTENTE->value => '#fbbf24', // Jaune
            statutdedemande::ACCEPTE->value => '#10b981', // Vert
            statutdedemande::REFUSE->value => '#ef4444', // Rouge
            statutdedemande::ANNULEE->value => '#6b7280', // Gris
            statutdedemande::CONSOMME->value => '#8b5cf6', // Violet
            statutdedemande::ANNULATIONENATTENTE->value => '#f97316', // Orange
        ];

        foreach ($conges as $conge) {
            $user = $conge->getUser();
            $status = $conge->getStatut();

            $events[] = [
                'id' => $conge->getId(),
                'title' => $user->getPrenom() . ' ' . $user->getNom() . ' (' . $status->label() . ')',
                'start' => $conge->getDateDebut()->format('Y-m-d'),
                'end' => $conge->getDateFin()->modify('+1 day')->format('Y-m-d'),
                'color' => $statusColors[$status->value] ?? '#008080',
                'extendedProps' => [
                    'status' => $status->value,
                    'statusLabel' => $status->label(),
                    'typeConge' => $conge->getTypeConge()->value,
                    'motif' => $conge->getMotif(),
                    'userName' => $user->getPrenom() . ' ' . $user->getNom()
                ]
            ];
        }

        return $events;
    }
}
