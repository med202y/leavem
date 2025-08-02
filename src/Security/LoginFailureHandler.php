<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Add a flash message for the user
        $request->getSession()->getFlashBag()->add('error', 'Invalid credentials. Please try again.');

        // Log the failed attempt (optional)
        // You could inject a logger and log the attempt here

        // You can customize the message based on the exception type
        $customMessage = $this->getCustomErrorMessage($exception);
        if ($customMessage) {
            $request->getSession()->getFlashBag()->add('error', $customMessage);
        }

        // Redirect back to login page
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    private function getCustomErrorMessage(AuthenticationException $exception): ?string
    {
        // Customize messages based on different exception types
        $exceptionClass = get_class($exception);

        return match ($exceptionClass) {
            'Symfony\Component\Security\Core\Exception\BadCredentialsException' => 'Invalid username or password.',
            'Symfony\Component\Security\Core\Exception\DisabledException' => 'Your account has been disabled.',
            'Symfony\Component\Security\Core\Exception\AccountExpiredException' => 'Your account has expired.',
            'Symfony\Component\Security\Core\Exception\CredentialsExpiredException' => 'Your password has expired.',
            'Symfony\Component\Security\Core\Exception\AccountStatusException' => 'There is a problem with your account.',
            default => null,
        };
    }
}
