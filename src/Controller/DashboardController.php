<?php

namespace App\Controller;

use App\Entity\Leave;
use App\Entity\User;
use App\Entity\Notification;
use App\Enum\Role;
use App\Enum\Departement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

#[IsGranted('ROLE_EMPLOYEE')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    
    public function dashboard(EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $logger->warning('Dashboard access attempt without authentication.');
            return $this->redirectToRoute('app_login');
        }

        // --- Get All User's Leaves ---
        $leaves = $em->getRepository(Leave::class)->findBy(['user' => $user]);

        // --- Update statut to "consommée" if approuvée and dateFin < today ---
        $today = new \DateTimeImmutable('today');
        $updated = false;
        foreach ($leaves as $leave) {
    $statusValue = $leave->getStatus()->value;
    $startDate = $leave->getStartDate();
    $endDate = $leave->getEndDate();

    if ($statusValue === 'approuvée') {
        // Leave is in the past -> consommée
        if ($endDate < $today) {
            $logger->info(sprintf(
                'Congé consommé automatiquement: ID %d, User %s, Dates %s - %s',
                $leave->getId(),
                $user->getUserIdentifier(),
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ));
            $leave->setStatus(\App\Enum\RequestStatus::CONSOMME);
            $em->persist($leave);
            $updated = true;
        }
        // Leave is ongoing -> en cours
        elseif ($startDate <= $today && $endDate >= $today) {
            $logger->info(sprintf(
                'Congé en cours automatiquement: ID %d, User %s, Dates %s - %s',
                $leave->getId(),
                $user->getUserIdentifier(),
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ));
            $leave->setStatus(\App\Enum\RequestStatus::EN_COURS);
            $em->persist($leave);
            $updated = true;
        }
    }
}
        if ($updated) {
            $em->flush();
            $logger->info('Statut des congés mis à jour pour l\'utilisateur: ' . $user->getUserIdentifier());
        }

        // --- Get Notifications ---
        $notifications = $em->getRepository(Notification::class)
            ->findBy(['recipient' => $user], ['id' => 'DESC'],7);

        // --- Solde de congés ---
        $leaveBalance = $user->getLeaveBalance() ?? 0;

        // --- Demandes en attente ---
        $demandesEnAttente = array_filter($leaves, fn($leave) => $leave->getStatus  ()->value === 'en_attente');

        // --- Congés approuvés cette année ---
        $currentYear = (int)date('Y');
        $leavesApprouves = array_filter($leaves, function ($leave) use ($currentYear) {
            return $leave->getStatus()->value === 'approuvée' &&
                $leave->getStartDate()->format('Y') == $currentYear;
        });

        // --- Membres dans le même département ---
        $teamMembersCount = $em->getRepository(User::class)->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.department = :dep')
            ->setParameter('dep', $user->getDepartment())
            ->getQuery()
            ->getSingleScalarResult();

        // --- Prepare Dashboard Stats ---
        $dashboardStats = [
    [
        'label' => 'Solde de congés',
        'value' => $leaveBalance,
        'icon' => 'calendar-check',
        'color' => 'indigo',
        'unit' => 'Jours disponibles'
    ],
    [
        'label' => 'Demandes en attente',
        'value' => count($demandesEnAttente),
        'icon' => 'clock',
        'color' => 'yellow',
        'unit' => 'En attente de validation'
    ],
    [
        'label' => 'Congés approuvés',
        'value' => count($leavesApprouves),
        'icon' => 'check-circle',
        'color' => 'green',
        'unit' => 'Cette année'
    ],
    [
        'label' => 'Membres de l\'équipe',
        'value' => $teamMembersCount,
        'icon' => 'users',
        'color' => 'blue',
        'unit' => 'Dans le département'
    ]
];

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'notifications' => $notifications,
            'role' => $user->getRoles()[0],
            'department' => $user->getDepartment(),
            'dashboardStats' => $dashboardStats
        ]);
    }
}
