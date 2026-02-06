<?php

namespace App\Controller\Team;

use App\Entity\Leave;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\RequestStatus;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TeamRepository;

#[IsGranted('ROLE_MANAGER')]
class TeamLeavesController extends AbstractController
{
    #[Route('/manager_leaves', name: 'manager_leaves')]
    public function showTeamLeaves(TeamRepository $teamRepository): Response
    {
        $user = $this->getUser();

        $team = $user->getTeam();
        if (!$team) {
            throw new \LogicException('L\'utilisateur manager n\'a pas d\'équipe assignée.');
        }

        $leaves = $teamRepository->findLeavesByTeamOrderedByRequestDate($team);
        
        $cases = RequestStatus::cases();
        $statuses = array_map(fn($c) => $c->value, $cases);

        return $this->render('team/leaves.html.twig', [
            'leaves' => $leaves,
            'statuses' => $statuses,
            'statusColors' => RequestStatus::colorMap(),
        ]);
    }

    #[Route('/manager_leaves/update/{id}', name: 'manager_leave_update', methods: ['POST'])]
    public function updateLeaveStatus(
        Request $request,
        Leave $leave,
        EntityManagerInterface $em,
        NotificationRepository $notificationRepo
    ): JsonResponse {
        try {
            $manager = $this->getUser();
            $action = $request->request->get('action');
            $validActions = ['accept', 'refuse', 'approve_cancellation', 'reject_cancellation'];
            
            if (!in_array($action, $validActions, true)) {
                return $this->json([
                    'title' => 'Action non autorisée',
                    'detail' => 'L\'action demandée n\'est pas valide',
                    'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $leave->getUser();
            $dateRange = $leave->getStartDate()->format('d/m/Y')." au ".$leave->getEndDate()->format('d/m/Y');
            $userFullName = $user->getFirstName()." ".$user->getLastName();

            // ====== APPROVE CANCELLATION ======
            if ($action === 'approve_cancellation') {
                if ($leave->getStatus() !== RequestStatus::ANNULATIONENATTENTE) {
                    return $this->json([
                        'title' => 'Statut invalide',
                        'detail' => 'Seules les demandes en attente d\'annulation peuvent être approuvées',
                        'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1'
                    ], Response::HTTP_BAD_REQUEST);
                }

                $leave
                    ->setStatus(RequestStatus::ANNULEE)
                    ->setProcessDate(new \DateTime())
                    ->setProcessedBy($manager->getFirstName().' '.$manager->getLastName());

                // Restore leave balance if annual leave
                if ($leave->getLeaveType()->value === 'congé annuel') {
                    $days = $leave->getStartDate()->diff($leave->getEndDate())->days + 1;
                    $user->setLeaveBalance($user->getLeaveBalance() + $days);
                }

                $notificationRepo->createAndPersistNotification(
                    $user,
                    'Annulation de congé approuvée',
                    "Votre demande d'annulation de congé du $dateRange a été approuvée."
                );

            // ====== REJECT CANCELLATION ======
            } elseif ($action === 'reject_cancellation') {
                if ($leave->getStatus() !== RequestStatus::ANNULATIONENATTENTE) {
                    return $this->json([
                        'title' => 'Statut invalide',
                        'detail' => 'Seules les demandes en attente d\'annulation peuvent être rejetées',
                        'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1'
                    ], Response::HTTP_BAD_REQUEST);
                }

                $leave
                    ->setStatus(RequestStatus::ACCEPTE)
                    ->setProcessDate(new \DateTime())
                    ->setProcessedBy($manager->getFirstName().' '.$manager->getLastName());

                $notificationRepo->createAndPersistNotification(
                    $user,
                    'Annulation de congé refusée',
                    "Votre demande d'annulation de congé du $dateRange a été refusée."
                );

            // ====== ACCEPT / REFUSE REQUEST ======
            } else {
                if ($leave->getStatus() !== RequestStatus::EN_ATTENTE) {
                    return $this->json([
                        'title' => 'Statut invalide',
                        'detail' => 'Seules les demandes en attente peuvent être traitées',
                        'type' => 'https://tools.ietf.org/html/rfc7231#section-6.5.1'
                    ], Response::HTTP_BAD_REQUEST);
                }

                $newStatus = $action === 'accept' ? RequestStatus::ACCEPTE : RequestStatus::REFUSE;
                $leave
                    ->setStatus($newStatus)
                    ->setProcessDate(new \DateTime())
                    ->setProcessedBy($manager->getFirstName().' '.$manager->getLastName());

                if ($newStatus === RequestStatus::REFUSE) {
                    $leave->setRefusalReason($request->request->get('reason'));
                    $notificationTitle = 'Demande de congé refusée';
                } else {
                    // Deduct from leave balance if annual leave
                    if ($leave->getLeaveType()->value === 'congé annuel') {
                        $days = $leave->getStartDate()->diff($leave->getEndDate())->days + 1;
                        $user->setLeaveBalance($user->getLeaveBalance() - $days);
                    }
                    $notificationTitle = 'Demande de congé acceptée';
                }

                $notificationRepo->createAndPersistNotification(
                    $user,
                    $notificationTitle,
                    "Votre demande de congé du $dateRange a été ".($newStatus === RequestStatus::ACCEPTE ? 'acceptée' : 'refusée')."."
                );
            }

            // Create manager notification for all actions
            $notificationRepo->createAndPersistNotification(
                $manager,
                'Action effectuée : '.ucfirst(str_replace('_', ' ', $action)),
                "Vous avez ".str_replace('_', ' ', $action)." le congé de $userFullName."
            );

            $em->flush();
            
            return $this->json([
                'data' => [
                    'id' => $leave->getId(),
                    'status' => $leave->getStatus()->value,
                    'processed_by' => $leave->getProcessedBy(),
                    'process_date' => $leave->getProcessDate()?->format('Y-m-d H:i:s'),
                ],
                'meta' => [
                    'message' => 'Action effectuée avec succès',
                    'action' => $action
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'title' => 'Erreur serveur',
                'detail' => 'Une erreur inattendue est survenue: '.$e->getMessage(),
                'type' => 'https://tools.ietf.org/html/rfc7231#section-6.6.1'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}