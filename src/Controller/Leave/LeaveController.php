<?php

// src/Controller/CongeController.php
namespace App\Controller\Leave;

use App\Entity\Conge;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\statutdedemande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LeaveController extends AbstractController
{
    #[Route('/my-leaves', name: 'my_leaves')]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function myLeaves(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $conges = $entityManager->getRepository(Conge::class)
            ->findBy(['user' => $user], ['dateDemande' => 'DESC']);

        // Prepare all status values and colors
        $allStatuses = statutdedemande::cases();
        $statusLabels = array_map(fn($s) => $s->value, $allStatuses);
        $statusColors = [
            statutdedemande::EN_ATTENTE->value => 'warning',
            statutdedemande::ACCEPTE    ->value  => 'success',
            statutdedemande::REFUSE->value    => 'danger',
            statutdedemande::ANNULEE->value    => 'secondary',
            statutdedemande::CONSOMME->value    => 'secondary',
            statutdedemande::ANNULATIONENATTENTE->value => 'warning',
        ];

        return $this->render('Leave/my_leaves.html.twig', [
            'conges'       => $conges,
            'statuses'     => $statusLabels,
            'statusColors' => $statusColors,
        ]);
    }

    #[Route('/edit-conge/{id}', name: 'edit_conge', methods: ['POST'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function editConge(
        Request $request,
        Conge $conge,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        // Sécurité : seul le propriétaire peut modifier
        if ($conge->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Seulement si EN_ATTENTE
        if ($conge->getStatut() !== statutdedemande::EN_ATTENTE) {
            return new JsonResponse(['success' => false, 'message' => 'Seuls les congés en attente peuvent être modifiés'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['dateDebut']) || empty($data['dateFin'])) {
            return new JsonResponse(['success' => false, 'message' => 'Dates manquantes'], 400);
        }

        try {
            $conge->setDateDebut(new \DateTime($data['dateDebut']));
            $conge->setDateFin(new \DateTime($data['dateFin']));

            // Notification employé
            $notifEmp = new Notification();
            $notifEmp->setTitle('Demande de congé modifiée');
            $notifEmp->setMessage("Votre demande de congé a été modifiée. Nouvelle période : " .
                $conge->getDateDebut()->format('d/m/Y') . " au " .
                $conge->getDateFin()->format('d/m/Y') . ".");
            $notifEmp->setRecipient($user);

            // Notification manager
            $manager = $user->getManager();
            if ($manager) {
                $notifMan = new Notification();
                $notifMan->setTitle('Action : Congé modifié');
                $notifMan->setMessage($user->getPrenom() . " " . $user->getNom() .
                    " a modifié sa demande de congé. Nouvelle période : " .
                    $conge->getDateDebut()->format('d/m/Y') . " au " .
                    $conge->getDateFin()->format('d/m/Y') . ".");
                $notifMan->setRecipient($manager);
                $em->persist($notifMan);
            }

            $em->persist($notifEmp);
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/cancel-conge/{id}', name: 'cancel_conge', methods: ['POST'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function cancelConge(
        Conge $conge,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        // Sécurité
        if ($conge->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Seulement si EN_ATTENTE ou ACCEPTE
        if (!in_array($conge->getStatut(), [statutdedemande::EN_ATTENTE, statutdedemande::ACCEPTE])) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Seuls les congés en attente ou approuvés peuvent être annulés'],
                400
            );
        }

        try {
            $manager = $user->getManager();

            // Si congé approuvé → Annulation en attente
            if ($conge->getStatut() === statutdedemande::ACCEPTE) {
                $conge->setStatut(statutdedemande::ANNULATIONENATTENTE);

                // Notification employé
                $notifEmp = new Notification();
                $notifEmp->setTitle('Demande d\'annulation envoyée');
                $notifEmp->setMessage("Votre demande d'annulation du congé du " .
                    $conge->getDateDebut()->format('d/m/Y') . " au " .
                    $conge->getDateFin()->format('d/m/Y') . " est en attente de validation.");
                $notifEmp->setRecipient($user);

                // Notification manager
                if ($manager) {
                    $notifMan = new Notification();
                    $notifMan->setTitle('Action requise : Annulation à valider');
                    $notifMan->setMessage($user->getPrenom() . " " . $user->getNom() .
                        " a demandé l'annulation de son congé (du " .
                        $conge->getDateDebut()->format('d/m/Y') . " au " .
                        $conge->getDateFin()->format('d/m/Y') . ").");
                    $notifMan->setRecipient($manager);
                    $em->persist($notifMan);
                }

                $em->persist($notifEmp);

            } else {
                // Si congé en attente → Annulation directe
                $conge->setStatut(statutdedemande::ANNULEE);

                // Notification employé
                $notifEmp = new Notification();
                $notifEmp->setTitle('Annulation de congé confirmée');
                $notifEmp->setMessage("Votre demande de congé a été annulée avec succès.");
                $notifEmp->setRecipient($user);

                // Notification manager
                if ($manager) {
                    $notifMan = new Notification();
                    $notifMan->setTitle('Information : Congé annulé');
                    $notifMan->setMessage($user->getPrenom() . " " . $user->getNom() .
                        " a annulé sa demande de congé (du " .
                        $conge->getDateDebut()->format('d/m/Y') . " au " .
                        $conge->getDateFin()->format('d/m/Y') . ").");
                    $notifMan->setRecipient($manager);
                    $em->persist($notifMan);
                }

                $em->persist($notifEmp);
            }

            $em->flush();
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
