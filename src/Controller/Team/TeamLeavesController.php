<?php

namespace App\Controller\Team;

use App\Entity\Conge;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\StatutDeDemande;
use App\Repository\CongeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TeamLeavesController extends AbstractController
{
    #[Route('/manager_leaves', name: 'manager_team_leaves')]
    #[IsGranted('ROLE_MANAGER')]
    public function showTeamLeaves(EntityManagerInterface $em, CongeRepository $congeRepository): Response
    {
        $user = $this->getUser();

        $team = $user->getTeam();
        if (!$team) {
            throw new \LogicException('L\'utilisateur manager n\'a pas d\'équipe assignée.');
        }

        $conges = $congeRepository->createQueryBuilder('c')
            ->join('c.user', 'u')
            ->where('u.team = :team')
            ->setParameter('team', $team)
            ->orderBy('c.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
        $cases = StatutDeDemande::cases();
        $statuses = array_map(fn($c) => $c->value, $cases);

        // 2) Map each statut to a Tailwind color (choose your shades)
        $statusColors = [
            StatutDeDemande::EN_ATTENTE->value => 'yellow-300',
            StatutDeDemande::ACCEPTE->value => 'green-300',
            StatutDeDemande::REFUSE->value => 'red-300',
            StatutDeDemande::ANNULEE->value => 'gray-300',
            StatutDeDemande::CONSOMME->value => 'dark-300',
            statutdedemande::ANNULATIONENATTENTE->value => 'warning',
        ];

        return $this->render('team\leaves.html.twig', [
            'conges' => $conges,
            'statuses' => $statuses,
            'statusColors' => $statusColors,
        ]);
    }

    #[Route('/manager_leaves/update/{id}', name: 'manager_conge_update', methods: ['POST'])]
    public function updateCongeStatus(
        Request                $request,
        Conge                  $conge,
        EntityManagerInterface $em,
        SessionInterface       $session
    ): JsonResponse
    {
        $manager = $this->getUser();
        if (!$manager) {
            return new JsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
        }
        if (!in_array('ROLE_MANAGER', $manager->getRoles(), true) && !in_array('ROLE_ADMIN', $manager->getRoles(), true)) {
            return new JsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        $action = $request->request->get('action');
        $validActions = ['accept', 'refuse', 'approve_cancellation', 'reject_cancellation'];
        if (!in_array($action, $validActions, true)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }

        try {
            $user = $conge->getUser();

            // ====== APPROUVER ANNULATION ======
            if ($action === 'approve_cancellation') {
                if ($conge->getStatut() !== StatutDeDemande::ANNULATIONENATTENTE) {
                    return new JsonResponse(['success' => false, 'message' => 'Seules les demandes en attente d\'annulation peuvent être approuvées'], 400);
                }

                $conge
                    ->setStatut(StatutDeDemande::ANNULEE)
                    ->setDateTraitement(new \DateTime())
                    ->setTraitePar($manager->getPrenom() . ' ' . $manager->getNom());

                // Notification employé
                $notifEmp = new Notification();
                $notifEmp->setTitle('Annulation de congé approuvée');
                $notifEmp->setMessage("Votre demande d'annulation de congé du " .
                    $conge->getDateDebut()->format('d/m/Y') . " au " .
                    $conge->getDateFin()->format('d/m/Y') . " a été approuvée.");
                $notifEmp->setRecipient($user);

                // Notification manager
                $notifMan = new Notification();
                $notifMan->setTitle('Action effectuée : Annulation approuvée');
                $notifMan->setMessage("Vous avez approuvé l'annulation du congé de " .
                    $user->getPrenom() . " " . $user->getNom() . ".");
                $notifMan->setRecipient($manager);

                $em->persist($notifEmp);
                $em->persist($notifMan);

                // Restitution des jours
                if ($conge->getTypeConge()->value === 'congé annuel') {
                    $days = $conge->getDateDebut()->diff($conge->getDateFin())->days + 1;
                    $user->setSoldeConge($user->getSoldeConge() + $days);
                }

                // ====== REFUSER ANNULATION ======
            } elseif ($action === 'reject_cancellation') {
                if ($conge->getStatut() !== StatutDeDemande::ANNULATIONENATTENTE) {
                    return new JsonResponse(['success' => false, 'message' => 'Seules les demandes en attente d\'annulation peuvent être rejetées'], 400);
                }

                $conge
                    ->setStatut(StatutDeDemande::ACCEPTE)
                    ->setDateTraitement(new \DateTime())
                    ->setTraitePar($manager->getPrenom() . ' ' . $manager->getNom());

                // Notification employé
                $notifEmp = new Notification();
                $notifEmp->setTitle('Annulation de congé refusée');
                $notifEmp->setMessage("Votre demande d'annulation de congé du " .
                    $conge->getDateDebut()->format('d/m/Y') . " au " .
                    $conge->getDateFin()->format('d/m/Y') . " a été refusée.");
                $notifEmp->setRecipient($user);

                // Notification manager
                $notifMan = new Notification();
                $notifMan->setTitle('Action effectuée : Annulation refusée');
                $notifMan->setMessage("Vous avez refusé l'annulation du congé de " .
                    $user->getPrenom() . " " . $user->getNom() . ".");
                $notifMan->setRecipient($manager);

                $em->persist($notifEmp);
                $em->persist($notifMan);

                // ====== ACCEPTER / REFUSER DEMANDE ======
            } else {
                if ($conge->getStatut() !== StatutDeDemande::EN_ATTENTE) {
                    return new JsonResponse(['success' => false, 'message' => 'Seules les demandes en attente peuvent être traitées'], 400);
                }

                $newStatut = $action === 'accept'
                    ? StatutDeDemande::ACCEPTE
                    : StatutDeDemande::REFUSE;

                $conge
                    ->setStatut($newStatut)
                    ->setDateTraitement(new \DateTime())
                    ->setTraitePar($manager->getPrenom() . ' ' . $manager->getNom());

                // Set refusal reason if action is refuse
                if ($newStatut === StatutDeDemande::REFUSE) {
                    $reason = $request->request->get('reason');
                    $conge->setRefusalReason($reason);
                }

                $notifEmp = new Notification();
                $notifMan = new Notification();

                if ($newStatut === StatutDeDemande::ACCEPTE) {
                    // Notification employé
                    $notifEmp->setTitle('Demande de congé acceptée');
                    $notifEmp->setMessage("Votre demande de congé du " .
                        $conge->getDateDebut()->format('d/m/Y') . " au " .
                        $conge->getDateFin()->format('d/m/Y') . " a été acceptée.");
                    $notifEmp->setRecipient($user);

                    // Notification manager
                    $notifMan->setTitle('Action effectuée : Congé accepté');
                    $notifMan->setMessage("Vous avez accepté la demande de congé de " .
                        $user->getPrenom() . " " . $user->getNom() . ".");
                    $notifMan->setRecipient($manager);

                    // Déduction du solde
                    if ($conge->getTypeConge()->value === 'congé annuel') {
                        $days = $conge->getDateDebut()->diff($conge->getDateFin())->days + 1;
                        $user->setSoldeConge($user->getSoldeConge() - $days);
                    }
                } else {
                    // Notification employé
                    $notifEmp->setTitle('Demande de congé refusée');
                    $notifEmp->setMessage("Votre demande de congé du " .
                        $conge->getDateDebut()->format('d/m/Y') . " au " .
                        $conge->getDateFin()->format('d/m/Y') . " a été refusée.");
                    $notifEmp->setRecipient($user);

                    // Notification manager
                    $notifMan->setTitle('Action effectuée : Congé refusé');
                    $notifMan->setMessage("Vous avez refusé la demande de congé de " .
                        $user->getPrenom() . " " . $user->getNom() . ".");
                    $notifMan->setRecipient($manager);
                }

                $em->persist($conge); 
                $em->persist($notifEmp);
                $em->persist($notifMan);
            }

            $em->flush();
            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
