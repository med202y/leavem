<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\Departement;
use App\Repository\CongeRepository;
use App\Repository\UserRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class adminuserscontroller extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/adminusers', name: 'admin_users_index', methods: ['GET'])]
    public function index(
        SessionInterface $session,
        EntityManagerInterface $em,
        CongeRepository $congeRepository
    ): Response
    {
        $userId = $session->get('user_id');

        // Vérifier qu'il est connecté
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            $session->remove('user_id');
            return $this->redirectToRoute('custom_login');
        }

        // Vérifier le rôle "BIGADMIN" (qui correspond à ROLE_ADMIN)
        $userRoles = $user->getRoles();
        if (!in_array('BIGADMIN', $userRoles, true) && !in_array('ROLE_ADMIN', $userRoles, true)) {
            throw new AccessDeniedException('Vous devez être administrateur pour accéder à cette page.');
        }

        $users = $this->userRepository->findAll();
        $teams = $this->teamRepository->findAll();

        return $this->render('adminusers.html.twig', [
            'users' => $users,
            'teams' => $teams,
            'departments' => Departement::cases(),
            'roles' => Role::cases(),
            'current_user' => $user,
        ]);
    }

    #[Route('/adminusers/create', name: 'admin_users_create', methods: ['POST'])]
    public function create(Request $request, SessionInterface $session): Response
    {
        // Check authentication
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $user = new User();

        // Récupération des données du formulaire
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $soldeConge = (float) $request->request->get('solde_conge', 25.0);
        $departement = $request->request->get('departement');
        $roles = $request->request->all('roles') ?? [];
        $teamId = $request->request->get('team_id');
        $observations = $request->request->get('observations');
        $dateEmbauche = $request->request->get('date_embauche');

        // Validation de base
        if (!$nom || !$prenom || !$email || !$password) {
            $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Vérifier si l'email existe déjà
        if ($this->userRepository->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
            return $this->redirectToRoute('admin_users_index');
        }

        try {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setSoldeConge($soldeConge);

            // Hash du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setMotDePasse($hashedPassword);

            // Département
            if ($departement) {
                $departementEnum = Departement::from($departement);
                $user->setDepartement($departementEnum);
            }

            // Rôles - Keep your existing enum-based approach
            if (!empty($roles)) {
                $roleEnums = [];
                foreach ($roles as $role) {
                    $roleEnums[] = Role::from($role);
                }
                $user->setRole($roleEnums);
            } else {
                // Set default role if none provided
                $user->setRole([Role::EMPLOYEE]);
            }

            // Équipe
            if ($teamId) {
                $team = $this->teamRepository->find($teamId);
                if ($team) {
                    $user->setTeam($team);
                }
            }

            // Observations
            if ($observations) {
                $user->setObservations($observations);
            }

            // Date d'embauche
            if ($dateEmbauche) {
                $user->setDateDembauche(new \DateTime($dateEmbauche));
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/adminusers/{id}/update', name: 'admin_users_update', methods: ['POST'])]
    public function update(int $id, Request $request, SessionInterface $session): Response
    {
        // Check authentication
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Prevent admin from modifying their own account (optional)
        $currentUser = $this->entityManager->getRepository(User::class)->find($userId);
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        try {
            // Mise à jour des propriétés
            if ($request->request->has('nom')) {
                $user->setNom($request->request->get('nom'));
            }

            if ($request->request->has('prenom')) {
                $user->setPrenom($request->request->get('prenom'));
            }

            if ($request->request->has('email')) {
                $email = $request->request->get('email');
                // Vérifier si l'email n'est pas déjà utilisé par un autre utilisateur
                $existingUser = $this->userRepository->findOneBy(['email' => $email]);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    $this->addFlash('error', 'Cet email est déjà utilisé par un autre utilisateur.');
                    return $this->redirectToRoute('admin_users_index');
                }
                $user->setEmail($email);
            }

            if ($request->request->has('solde_conge')) {
                $user->setSoldeConge((float) $request->request->get('solde_conge'));
            }

            if ($request->request->has('departement')) {
                $departement = $request->request->get('departement');
                if ($departement) {
                    $user->setDepartement(Departement::from($departement));
                }
            }

            if ($request->request->has('roles')) {
                $roles = $request->request->all('roles') ?? [];
                if (!empty($roles)) {
                    $roleEnums = [];
                    foreach ($roles as $role) {
                        $roleEnums[] = Role::from($role);
                    }
                    $user->setRole($roleEnums);
                }
            }

            if ($request->request->has('team_id')) {
                $teamId = $request->request->get('team_id');
                if ($teamId) {
                    $team = $this->teamRepository->find($teamId);
                    $user->setTeam($team);
                } else {
                    $user->setTeam(null);
                }
            }

            if ($request->request->has('observations')) {
                $user->setObservations($request->request->get('observations'));
            }

            if ($request->request->has('date_embauche')) {
                $dateEmbauche = $request->request->get('date_embauche');
                if ($dateEmbauche) {
                    $user->setDateDembauche(new \DateTime($dateEmbauche));
                }
            }

            // Mise à jour du mot de passe si fourni
            if ($request->request->get('password')) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $request->request->get('password'));
                $user->setMotDePasse($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/adminusers/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(int $id, SessionInterface $session): Response
    {
        // Check authentication
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Prevent admin from deleting their own account
        $currentUser = $this->entityManager->getRepository(User::class)->find($userId);
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/adminusers/{id}/toggle-status', name: 'admin_users_toggle_status', methods: ['POST'])]
    public function toggleStatus(int $id, SessionInterface $session): Response
    {
        // Check authentication
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Prevent admin from disabling their own account
        $currentUser = $this->entityManager->getRepository(User::class)->find($userId);
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier le statut de votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Assuming you have an 'enabled' field in your User entity
        // $user->setEnabled(!$user->isEnabled());
        // $this->entityManager->flush();

        $this->addFlash('success', 'Statut de l\'utilisateur modifié.');
        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/adminusers/bulk-update-solde', name: 'admin_users_bulk_update_solde', methods: ['POST'])]
    public function bulkUpdateSolde(Request $request, SessionInterface $session): Response
    {
        // Check authentication
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('custom_login');
        }

        $userIds = $request->request->all('user_ids');
        $newSolde = (float) $request->request->get('new_solde');

        if (!$userIds || $newSolde < 0) {
            $this->addFlash('error', 'Données invalides pour la mise à jour en lot.');
            return $this->redirectToRoute('admin_users_index');
        }

        try {
            $users = $this->userRepository->findBy(['id' => $userIds]);
            $currentUser = $this->entityManager->getRepository(User::class)->find($userId);

            foreach ($users as $user) {
                // Skip the current admin user for safety
                if ($user !== $currentUser) {
                    $user->setSoldeConge($newSolde);
                }
            }

            $this->entityManager->flush();

            $updatedCount = count($users);
            if (in_array($userId, $userIds)) {
                $updatedCount--; // Exclude current user from count if they were in the list
            }

            $this->addFlash('success', $updatedCount . ' utilisateur(s) mis à jour avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour en lot: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_index');
    }
}
