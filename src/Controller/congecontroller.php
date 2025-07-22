<?php

// src/Controller/CongeController.php
namespace App\Controller;

use App\Entity\Conge;
use App\Entity\User;
use App\Enum\statutdedemande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class congecontroller extends AbstractController
{
    #[Route('/my-leaves', name: 'my_leaves')]
    public function myLeaves(
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ): Response {
        $userId = $session->get('user_id');

        // Handle unauthenticated users - for testing, use a default user
        if (!$userId) {
            $testUser = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => 'jean.dupont@example.com'])
                ?: $entityManager->getRepository(User::class)
                    ->findOneBy(['email' => 'claire.dubois@example.com']);

            if (!$testUser) {
                throw new AccessDeniedException('No test user found. Please run: php bin/console doctrine:fixtures:load');
            }

            $userId = $testUser->getId();
            $session->set('user_id', $userId);
        }

        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new AccessDeniedException('User not found');
        }

        $conges = $entityManager->getRepository(Conge::class)
            ->findBy(
                ['user' => $user],
                ['dateDemande' => 'DESC']
            );

        // Prepare all status values and colors
        $allStatuses = statutdedemande::cases();
        $statusLabels = array_map(fn($s) => $s->value, $allStatuses);
        $statusColors = [
            statutdedemande::EN_ATTENTE->value => 'warning',
            statutdedemande::APPROUVEE->value  => 'success',
            statutdedemande::REJETEE->value    => 'danger',
            statutdedemande::ANNULEE->value    => 'secondary',
        ];

        return $this->render('my_leaves.html.twig', [  // ensure correct path
            'conges'       => $conges,
            'statuses'     => $statusLabels,
            'statusColors' => $statusColors,
        ]);
    }
    #[Route('/edit-conge/{id}', name: 'edit_conge', methods: ['POST'])]
    public function editConge(Request $request, Conge $conge, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data['dateDebut'] || !$data['dateFin']) {
            return new JsonResponse(['success' => false, 'message' => 'Missing dates'], 400);
        }

        try {
            $conge->setDateDebut(new \DateTime($data['dateDebut']));
            $conge->setDateFin(new \DateTime($data['dateFin']));
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

// Cancel leave
    #[Route('/cancel-conge/{id}', name: 'cancel_conge', methods: ['POST'])]
    public function cancelConge(Conge $conge, EntityManagerInterface $em): JsonResponse
    {
        try {
            $conge->setStatut(\App\Enum\statutdedemande::ANNULEE);
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }




}
