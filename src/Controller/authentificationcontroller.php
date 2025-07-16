<?php

// src/Controller/SecurityController.php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\departement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class authentificationcontroller extends AbstractController
{
    #[Route('/', name: 'custom_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        SessionInterface $session
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$hasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Identifiants invalides');
                return $this->redirectToRoute('custom_login');
            }

            // Connexion manuelle
            $session->set('user_id', $user->getId());

            // Redirection selon rôle et département
            $role = $user->getRole()[0]; // enum Role
            $departement = $user->getDepartement(); // enum Departement

            if ($role === Role::EMPLOYEE && $departement === Departement::symfony) {
                return $this->redirectToRoute('employee_symfony');
            }
            if ($role === Role::MANAGER && $departement === Departement::symfony) {
                return $this->redirectToRoute('mananger_symfony');
            }

            if ($role === Role::EMPLOYE && $departement === Departement::integrateur) {
                return $this->redirectToRoute('employee_integrateur');
            }
            if ($role === Role::MANAGER && $departement === Departement::integrateur) {
                return $this->redirectToRoute('employee_integrateur');
            }


        }

        return $this->render('home/authentification.html.twig');
    }
}
