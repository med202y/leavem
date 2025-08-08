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
            $this->addFlash('error', 'Cette demande ne peut pas être modifiée');
            return $this->redirectToRoute('my_leaves');
        }

        try {
            $data = json_decode($request->getContent(), true);
            $startDate = new DateTime($data['startDate'] ?? '');
            $endDate = new DateTime($data['endDate'] ?? '');

            $this->validateLeaveDates($startDate, $endDate);
            
            $leave
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setRequestDate(new DateTime()); // Update request date

            $notificationRepo->createLeaveModifiedNotification($leave, $user);
            $em->flush();

            $this->addFlash('success', 'Congé modifié avec succès');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la modification');
        }
        // Redirect to the same page to see the updated leave
        return $this->redirectToRoute('my_leaves');
    }

    #[Route('/cancel-leave/{id}', name: 'cancel_leave', methods: ['POST'])]
    public function cancelLeave(
        Leave $leave,
        EntityManagerInterface $em,
        NotificationRepository $notificationRepo
    ): Response {
        $user = $this->getUser();

        if (!$this->isLeaveCancellable($leave, $user)) {
            $this->addFlash('error', 'Cette demande ne peut pas être annulée');
            return $this->redirectToRoute('my_leaves');
        }

        try {
            if ($leave->getStatus() === RequestStatus::ACCEPTE) {
                $leave->setStatus(RequestStatus::ANNULATIONENATTENTE);
                $message = 'Demande d\'annulation envoyée';
            } else {
                $leave->setStatus(RequestStatus::ANNULEE);
                $message = 'Congé annulé avec succès';
                $notificationRepo->createLeaveCancellationNotification($leave, $user);
            }

            $em->flush();
            $this->addFlash('success', $message);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'annulation');
        }

        return $this->redirectToRoute('my_leaves');
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