<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class demandecontroller extends AbstractController
{
    #[Route('/demande', name: 'demande', methods: ['GET'])]
     public function demande(
        SessionInterface $session,
        EntityManagerInterface $em
    ){
        $userId = $session->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);
        return $this->render('demande.html.twig',
        ['user' => $user,
            'role' => $user->getRole()[0],        // Adjust if Role is an array, otherwise just getRole()
            'departement' => $user->getDepartement(),]);
    }


}
