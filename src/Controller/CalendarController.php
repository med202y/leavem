<?php

namespace App\Controller;

use App\Entity\Leave;
use App\Enum\RequestStatus;
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
        // Récupérer les statuts sélectionnés depuis les paramètres de requête (par défaut, uniquement accepté)
        $selectedStatuses = $request->query->all('statuses') ?: [RequestStatus::ACCEPTE->value];

        // Convertir les valeurs string en enum cases
        $statusEnums = array_filter(
            RequestStatus::cases(),
            fn(RequestStatus $case) => in_array($case->value, $selectedStatuses, true)
        );

        // Requête pour récupérer les congés avec les statuts sélectionnés
        $qb = $em->getRepository(Leave::class)->createQueryBuilder('l');
        if (!empty($statusEnums)) {
            $qb->where($qb->expr()->in('l.status', ':statuses'))
               ->setParameter('statuses', $statusEnums);
        }
        $leaves = $qb->orderBy('l.requestDate', 'DESC')->getQuery()->getResult();

        $events = $this->buildEvents($leaves);

        // Préparer les données des statuts pour le template
        $allStatuses = array_map(
            fn(RequestStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'selected' => in_array($status->value, $selectedStatuses, true)
            ],
            RequestStatus::cases()
        );

        return $this->render('calendar/index.html.twig', [
            'events' => json_encode($events),
            'statuses' => $allStatuses,
            'selectedStatuses' => $selectedStatuses,
        ]);
    }

    #[Route('/calendar/events', name: 'app_calendar_events', methods: ['GET'])]
    public function getEvents(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $selectedStatuses = $request->query->all('statuses') ?: [];
        $statusEnums = array_filter(
            RequestStatus::cases(),
            fn(RequestStatus $case) => in_array($case->value, $selectedStatuses, true)
        );

        $qb = $em->getRepository(Leave::class)->createQueryBuilder('l');
        if (!empty($statusEnums)) {
            $qb->where($qb->expr()->in('l.status', ':statuses'))
               ->setParameter('statuses', $statusEnums);
        }
        $leaves = $qb->getQuery()->getResult();

        $events = $this->buildEvents($leaves);
        return new JsonResponse($events);
    }

    private function buildEvents(array $leaves): array
    {
        $events = [];
        $statusColors = [
            RequestStatus::EN_ATTENTE->value => '#fbbf24',
            RequestStatus::ACCEPTE->value => '#10b981',
            RequestStatus::REFUSE->value => '#ef4444',
            RequestStatus::ANNULEE->value => '#6b7280',
            RequestStatus::CONSOMME->value => '#8b5cf6',
            RequestStatus::ANNULATIONENATTENTE->value => '#f97316',
        ];

        foreach ($leaves as $leave) {
            $user = $leave->getUser();
            $status = $leave->getStatus();

            $events[] = [
                'id' => $leave->getId(),
                'title' => sprintf('%s %s (%s)', $user->getFirstName(), $user->getLastName(), $status->label()),
                'start' => $leave->getStartDate()->format('Y-m-d'),
                // FullCalendar end date is exclusive, donc on ajoute un jour
                'end' => $leave->getEndDate()->modify('+1 day')->format('Y-m-d'),
                'color' => $statusColors[$status->value] ?? '#008080',
                'extendedProps' => [
                    'status' => $status->value,
                    'statusLabel' => $status->label(),
                    'typeConge' => $leave->getLeaveType()->value,
                    'motif' => $leave->getReason(),
                    'userName' => $user->getFirstName() . ' ' . $user->getLastName(),
                ],
            ];
        }

        return $events;
    }
}
