<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Entity\User;
use App\Entity\Notification;
use App\Enum\Role;
use App\Enum\Departement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // --- Get All User's Congés ---
        $conges = $em->getRepository(Conge::class)->findBy(['user' => $user]);

        // --- Update statut to "consommée" if approuvée and dateFin < today ---
        $today = new \DateTimeImmutable('today');
        $updated = false;
        foreach ($conges as $conge) {
            if (
                $conge->getStatut()->value === 'approuvée'
                && $conge->getDateFin() < $today
            ) {
                $conge->setStatut(\App\Enum\statutdedemande::CONSOMME);
                $em->persist($conge);
                $updated = true;
            }
        }
        if ($updated) {
            $em->flush();
        }

        // --- Get Notifications ---
        $notifications = $em->getRepository(Notification::class)
            ->findBy(['recipient' => $user], ['id' => 'DESC']);

        // --- Solde de congés ---
        $soldeConge = $user->getSoldeConge() ?? 0;

        // --- Demandes en attente ---
        $demandesEnAttente = array_filter($conges, fn($conge) => $conge->getStatut()->value === 'en_attente');

        // --- Congés approuvés cette année ---
        $currentYear = (int)date('Y');
        $congesApprouves = array_filter($conges, function ($conge) use ($currentYear) {
            return $conge->getStatut()->value === 'approuvée' &&
                $conge->getDateDebut()->format('Y') == $currentYear;
        });

        // --- Membres dans le même département ---
        $teamMembersCount = $em->getRepository(User::class)->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.departement = :dep')
            ->setParameter('dep', $user->getDepartement())
            ->getQuery()
            ->getSingleScalarResult();

        // --- Prepare Dashboard Stats ---
        $dashboardStats = [
            [
                'label' => 'Solde de congés',
                'value' => $soldeConge,
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
                'value' => count($congesApprouves),
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
            'role' => $user->getRole()[0],
            'departement' => $user->getDepartement(),
            'dashboardStats' => $dashboardStats
        ]);
    }
}
