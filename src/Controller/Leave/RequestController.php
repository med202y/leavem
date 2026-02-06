<?php

namespace App\Controller\Leave;

use App\Entity\User;
use App\Entity\Leave;
use App\Entity\Notification;
use App\Repository\LeaveRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\LeaveFormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;
use App\Enum\RequestStatus;
#[IsGranted('ROLE_EMPLOYEE')]

class RequestController extends AbstractController
{
    #[Route('/requestleave', name: 'request_leave', methods: ['GET', 'POST'])]
  
    public function demande(
        EntityManagerInterface $em,
        Request $request,
        LoggerInterface $logger,
        NotificationRepository $notificationRepository,
    ) {
        $user = $this->getUser();

        $leave = new Leave(); // Changed from $conge to $leave
        $form = $this->createForm(LeaveFormType::class, $leave);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $leave->setUser($user);
            $leave->setRequestDate(new \DateTime()); // Changed from setDateDemande
            $leave->setStatus(RequestStatus::EN_ATTENTE); // Changed from setStatut

            // Handle proof file (changed from justificatif)
            /** @var UploadedFile $file */
            $file = $form->get('proof')->getData(); // Changed from justificatif
            if ($file) {
                $imagesDirectory = $this->getParameter('kernel.project_dir') . '/public/images';
                $filename = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move($imagesDirectory, $filename);
                    $leave->setProof($filename); // Changed from setJustificatif
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload du fichier.");
                    return $this->redirectToRoute('demande');
                }
            }

            $em->persist($leave);
            $em->flush();

            $logger->info(sprintf(
                'Nouvelle demande de congé: User %s, Dates %s - %s',
                $user->getUserIdentifier(),
                $leave->getStartDate()->format('Y-m-d'), 
                $leave->getEndDate()->format('Y-m-d') 
            ));
            $notificationRepository->createLeaveModifiedNotifications($leave, $user); 

            $this->addFlash('success', 'Demande de congé enregistrée avec succès.');
            return $this->redirectToRoute('dashboard');
        }

        // Get the first role (assuming the user has at least one role)
        $roles = $user->getRoles();
        $primaryRole = !empty($roles) ? $roles[0] : 'ROLE_EMPLOYEE';

        return $this->render('demand/index.html.twig', [
            'user' => $user,
            'role' => $primaryRole,
            'departement' => $user->getDepartment()?->value,
            'form' => $form->createView(),
        ]);
    }
}