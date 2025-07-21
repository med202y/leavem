<?php

// src/Controller/CongeController.php
namespace App\Controller;

use App\Entity\Conge;
use App\Entity\Notification;
use App\Enum\statutdedemande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security; // Updated namespace

class congecontroller extends AbstractController // Fixed class name casing
{
    private $security;
    private $entityManager;

    public function __construct(
        Security $security, // Updated type hint
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/my-leaves', name: 'my_leaves')]
    public function myLeaves(): Response
    {
        $user = $this->security->getUser();
        $conges = $this->entityManager->getRepository(Conge::class)->findBy(['user' => $user]);


        return $this->render('demandes.html.twig', [
            'conges' => $conges,
        ]);
    }

    #[Route('/update-leave/{id}', name: 'update_leave', methods: ['POST'])]
    public function updateLeave(Request $request, Conge $conge): JsonResponse
    {
        $user = $this->security->getUser();

        // Verify ownership and status
        if ($conge->getUser()->getId() !== $user->getId() ||
            $conge->getStatut() !== statutdedemande::EN_ATTENTE) {
            return new JsonResponse(['status' => 'error', 'message' => 'Unauthorized action']);
        }

        $data = json_decode($request->getContent(), true);

        // Update leave dates
        $conge->setDateDebut(new \DateTime($data['date_debut']));
        $conge->setDateFin(new \DateTime($data['date_fin']));

        // Create notification
        $notification = new Notification();
        $notification->setTitle('Leave Request Updated');
        $notification->setMessage(sprintf(
            'Your leave request from %s to %s has been modified',
            $data['date_debut'],
            $data['date_fin']
        ));
        $notification->setRecipient($user);
        $notification->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'newDateDebut' => $conge->getDateDebut()->format('Y-m-d'),
            'newDateFin' => $conge->getDateFin()->format('Y-m-d')
        ]);
    }
}
