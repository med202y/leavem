<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Get the authenticated user
        $user = $token->getUser();

        // You can add role-based redirections if needed
        // if (in_array('ROLE_ADMIN', $user->getRoles())) {
        //     return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        // }

        // Default redirect to dashboard
        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }
}
