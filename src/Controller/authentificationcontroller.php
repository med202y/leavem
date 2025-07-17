<?php

// src/Controller/SecurityController.php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\Departement;
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

            // Redirect to dashboard (single route for all roles)
            return $this->redirectToRoute('dashboard');


        }

        return $this->render('security/authentification.html.twig');
    }
}
