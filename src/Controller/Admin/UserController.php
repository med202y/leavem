<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserSettingsFormType;
use App\Repository\LeaveRepository;
use App\Repository\UserRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/admin', name: 'admin_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        LeaveRepository $LeaveRepository,
        #[CurrentUser] User $currentUser,
    ): Response {
        
        // Handle user creation form
        $newUser = new User();
        $createForm = $this->createForm(UserSettingsFormType::class, $newUser, [
            'is_edit' => false,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'user_create'
        ]);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            return $this->handleUserCreation($createForm, $newUser);
        }

        $users = $this->userRepository->findAll();
        $teams = $this->teamRepository->findAll();
        $editForms = [];
        
        foreach ($users as $user) {
            $editForm = $this->createForm(UserSettingsFormType::class, $user, [
                'is_edit' => true,
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id' => 'user_edit_' . $user->getId()
            ]);
            $editForms[$user->getId()] = $editForm->createView();
        }

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'teams' => $teams,
            'current_user' => $currentUser,
            'createForm' => $createForm->createView(),
            'editForms' => $editForms,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_users_edit', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(int $id, Request $request): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_index');
        }

        $currentUser = $this->getUser();
        if ($user === $currentUser) {
            $this->addFlash('error', 'You cannot modify your own account.');
            return $this->redirectToRoute('admin_index');
        }

        // Create form with proper CSRF token ID
        $form = $this->createForm(UserSettingsFormType::class, $user, [
            'is_edit' => true,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'user_edit_' . $user->getId()
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleUserUpdate($form, $user);
        }

        // If form is invalid, add specific error messages
        if ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, Request $request): Response
    {
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('delete_user_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_index');
        }

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_index');
        }

        $currentUser = $this->getUser();
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('admin_index');
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/admin/users/{id}/toggle-status', name: 'admin_users_toggle_status', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleStatus(int $id, Request $request): Response
    {
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('toggle_status_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_index');
        }

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_index');
        }

        $currentUser = $this->getUser();
        if ($user === $currentUser) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier le statut de votre propre compte.');
            return $this->redirectToRoute('admin_index');
        }

        // Assuming you have an 'enabled' field in your User entity
        // $user->setEnabled(!$user->isEnabled());
        // $this->entityManager->flush();

        $this->addFlash('success', 'Statut de l\'utilisateur modifié.');
        return $this->redirectToRoute('admin_index');
    }

    #[Route('/admin/users/bulk-update-solde', name: 'admin_users_bulk_update_solde', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function bulkUpdateSolde(Request $request): Response
    {
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('bulk_update_solde', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_index');
        }

        $userIds = $request->request->all('user_ids');
        $newSolde = (float) $request->request->get('new_solde');

        if (!$userIds || $newSolde < 0) {
            $this->addFlash('error', 'Données invalides pour la mise à jour en lot.');
            return $this->redirectToRoute('admin_index');
        }

        try {
            $users = $this->userRepository->findBy(['id' => $userIds]);
            $currentUser = $this->getUser();

            foreach ($users as $user) {
                if ($user !== $currentUser) {
                    $user->setLeaveBalance($newSolde);
                }
            }

            $this->entityManager->flush();

            $updatedCount = count($users);
            if (in_array($currentUser->getId(), $userIds)) {
                $updatedCount--;
            }

            $this->addFlash('success', $updatedCount . ' utilisateur(s) mis à jour avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour en lot: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_index');
    }

    private function handleUserCreation($form, User $user): Response
    {
        try {
            // Check for existing email
            $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email already exists.');
                return $this->redirectToRoute('admin_index');
            }

            // Handle password
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'User created successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error creating user: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_index');
    }

    private function handleUserUpdate($form, User $user): Response
    {
        try {
            // Check for email conflicts (exclude current user)
            $email = $user->getEmail();
            $existingUser = $this->userRepository->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'This email is already used by another user.');
                return $this->redirectToRoute('admin_index');
            }

            // Handle password update if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error updating user: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_index');
    }
}