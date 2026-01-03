<?php

namespace App\Controller\Leave;

use App\Entity\Leave;
use App\Entity\User;
use App\Enum\RequestStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\LeaveRepository;
use App\Repository\NotificationRepository;
use DateTime;
 #[IsGranted('ROLE_EMPLOYEE')]
class LeaveController extends AbstractController
{
    #[Route('/my-leaves', name: 'my_leaves')]

    public function myLeaves(
        LeaveRepository $leaveRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getUser();
        $query = $leaveRepository->createQueryBuilderForUserOrderedByDate($user);
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );


        return $this->render('leave/my_leaves.html.twig', [
            'pagination' => $pagination,
            'statuses' => array_map(fn($s) => $s->value, RequestStatus::cases()),
            'statusColors' => RequestStatus::colorMap(),
        ]);
    }

     #[Route('/edit-leave/{id}', name: 'edit_leave', methods: ['POST'])]
     public function editLeave(
         Request $request,
         Leave $leave,
         EntityManagerInterface $em,
         NotificationRepository $notificationRepo
     ): Response {
         $user = $this->getUser();

         // Security checks
         if (!$this->isLeaveEditable($leave, $user)) {
             return $this->json([
                 'success' => false,
                 'message' => 'Cette demande ne peut pas être modifiée'
             ], 403);
         }

         try {
             $data = json_decode($request->getContent(), true);
             $startDate = new DateTime($data['startDate'] ?? '');
             $endDate = new DateTime($data['endDate'] ?? '');

             $this->validateLeaveDates($startDate, $endDate);

             $leave
                 ->setStartDate($startDate)
                 ->setEndDate($endDate)
                 ->setRequestDate(new DateTime());

             $notificationRepo->createLeaveModifiedNotifications($leave, $user);
             $em->flush();

             return $this->json([
                 'success' => true,
                 'message' => 'Congé modifié avec succès'
             ]);

         } catch (\InvalidArgumentException $e) {
             return $this->json([
                 'success' => false,
                 'message' => $e->getMessage()
             ], 400);
         } catch (\Exception $e) {
             return $this->json([
                 'success' => false,
                 'message' => 'Erreur lors de la modification'
             ], 500);
         }
     }

     #[Route('/cancel-leave/{id}', name: 'cancel_leave', methods: ['POST'])]
     public function cancelLeave(
         Leave $leave,
         EntityManagerInterface $em,
         NotificationRepository $notificationRepo
     ): Response {
         $user = $this->getUser();

         if (!$this->isLeaveCancellable($leave, $user)) {
             return $this->json([
                 'success' => false,
                 'message' => 'Cette demande ne peut pas être annulée'
             ], 403);
         }

         try {
             if ($leave->getStatus() === RequestStatus::ACCEPTE) {
                 $leave->setStatus(RequestStatus::ANNULATIONENATTENTE);
                 $message = 'Demande d\'annulation envoyée';
             } else {
                 $leave->setStatus(RequestStatus::ANNULEE);
                 $message = 'Congé annulé avec succès';
                 $notificationRepo->createLeaveCancellationNotifications($leave, $user);
             }

             $em->flush();

             return $this->json([
                 'success' => true,
                 'message' => $message
             ]);

         } catch (\Exception $e) {
             return $this->json([
                 'success' => false,
                 'message' => 'Erreur lors de l\'annulation'
             ], 500);
         }
     }
    private function isLeaveEditable(Leave $leave, User $user): bool
    {
        return $leave->getUser()->getId() === $user->getId()
            && in_array($leave->getStatus(), [
                RequestStatus::EN_ATTENTE,
                RequestStatus::ACCEPTE
            ])
            && $leave->getStartDate() > new DateTime('today');
    }

    private function isLeaveCancellable(Leave $leave, User $user): bool
    {
        return $leave->getUser()->getId() === $user->getId()
            && in_array($leave->getStatus(), [
                RequestStatus::EN_ATTENTE,
                RequestStatus::ACCEPTE
            ]);
    }

    private function validateLeaveDates(DateTime $startDate, DateTime $endDate): void
    {
        if ($startDate > $endDate) {
            throw new \InvalidArgumentException('La date de fin doit être postérieure à la date de début');
        }

        if ($startDate < new DateTime('today')) {
            throw new \InvalidArgumentException('La date de début ne peut pas être dans le passé');
        }
    }
}
