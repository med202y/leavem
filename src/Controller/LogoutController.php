<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'logout')]
    public function logout(SessionInterface $session): RedirectResponse
    {
        // Clear session data
        $session->clear();

        // Optionally, you can add a flash message
        $this->addFlash('success', 'Vous avez été déconnecté avec succès.');

        // Redirect to login page
        return $this->redirectToRoute('custom_login');
    }
}
