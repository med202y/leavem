<?php

namespace App\Controller\Leave;

use App\Entity\User;
use App\Entity\Conge;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\CongeType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;

class RequestController extends AbstractController
{
    #[Route('/demande', name: 'demande', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    public function demande(
        EntityManagerInterface $em,
        Request $request,
        LoggerInterface $logger
    ){
        $user = $this->getUser();

        $conge = new Conge();
        $form = $this->createForm(CongeType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conge->setUser($user);
            $conge->setDateDemande(new \DateTime());
            $conge->setStatut(\App\Enum\statutdedemande::EN_ATTENTE);

            // Handle justificatif file
            /** @var UploadedFile $file */
            $file = $form->get('justificatif')->getData();
            if ($file) {
                $imagesDirectory = $this->getParameter('kernel.project_dir') . '/public/images';
                $filename = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move($imagesDirectory, $filename);
                    $conge->setJustificatif($filename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload du fichier.");
                    return $this->redirectToRoute('demande');
                }
            }

            $em->persist($conge);
            $em->flush();

            $logger->info(sprintf(
                'Nouvelle demande de congé: User %s, Dates %s - %s',
                $user->getUserIdentifier(),
                $conge->getDateDebut()->format('Y-m-d'),
                $conge->getDateFin()->format('Y-m-d')
            ));

            // Notification for the user
            $notification = new Notification();
            $notification->setTitle('Nouvelle demande de congé');
            $notification->setMessage('Votre demande de congé du ' . $conge->getDateDebut()->format('d/m/Y') . ' au ' . $conge->getDateFin()->format('d/m/Y') . ' a été enregistrée.');
            $notification->setRecipient($user);
            $em->persist($notification);

            // Notification for the manager
            if ($user->getTeam() && $user->getTeam()->getManager()) {
                $manager = $user->getTeam()->getManager();
                $notifManager = new Notification();
                $notifManager->setTitle('Nouvelle demande de congé à valider');
                $notifManager->setMessage($user->getPrenom() . ' ' . $user->getNom() . ' a soumis une nouvelle demande de congé du ' . $conge->getDateDebut()->format('d/m/Y') . ' au ' . $conge->getDateFin()->format('d/m/Y') . '.');
                $notifManager->setRecipient($manager);
                $em->persist($notifManager);
            }

            $em->flush();

            $this->addFlash('success', 'Demande de congé enregistrée avec succès.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('demand/index.html.twig', [
            'user' => $user,
            'role' => $user->getRole()[0],
            'departement' => $user->getDepartement(),
            'form' => $form->createView(),
        ]);
    }
}
