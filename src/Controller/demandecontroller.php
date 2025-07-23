<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Conge;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class demandecontroller extends AbstractController
{
    #[Route('/demande', name: 'demande', methods: ['GET', 'POST'])]
    public function demande(
        SessionInterface $session,
        EntityManagerInterface $em,
        Request $request
    ){
        $userId = $session->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);

        if ($request->isMethod('POST')) {
            $dateDebut = $request->request->get('date_debut_conge');
            $dateFin = $request->request->get('date_fin_conge');
            $motif = $request->request->get('observations');
            $file = $request->files->get('justificatif');
            $typeConge = $request->request->get('type_conge'); // You need to add this field in your form if not present

            // Validation
            $error = null;
            if (!$dateDebut || !$dateFin) {
                $error = "Veuillez remplir toutes les dates obligatoires.";
            } elseif ($dateDebut > $dateFin) {
                $error = "La date de début doit être antérieure ou égale à la date de fin.";
            }

            if ($error) {
                $this->addFlash('error', $error);
                return $this->redirectToRoute('demande');
            }

            // Save to database
            $conge = new Conge();
            $conge->setDateDebut(new \DateTime($dateDebut));
            $conge->setDateFin(new \DateTime($dateFin));
            $conge->setMotif($motif);
            $conge->setDateDemande(new \DateTime());
            $conge->setStatut(\App\Enum\statutdedemande::EN_ATTENTE);
            $conge->setUser($user);


            if ($typeConge) {
                // Validate and convert string to enum case
                try {
                    $conge->setTypeConge(\App\Enum\typeconge::from($typeConge));
                } catch (\ValueError $e) {
                    $this->addFlash('error', 'Type de congé invalide.');
                    return $this->redirectToRoute('demande');
                }
            }

            // Add missing fields
            $dateTraitement = null; // Set this when the request is processed
            $conge->setDateTraitement($dateTraitement);

            $traitePar = ""; // Set this when the request is processed
            $conge->setTraitePar($traitePar);

            // Handle justificatif file
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
            $conge->setUser($user);
            $em->persist($conge);
            $em->flush();

            // Notification
            $notification = new Notification();
            $notification->setTitle('Nouvelle demande de congé');
            $notification->setMessage('Votre demande de congé du ' . $dateDebut . ' au ' . $dateFin . ' a été enregistrée.');
            $notification->setRecipient($user);

            $em->persist($notification);
            $em->flush();

            $this->addFlash('success', 'Demande de congé enregistrée avec succès.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('demande.html.twig', [
            'user' => $user,
            'role' => $user->getRole()[0],
            'departement' => $user->getDepartement(),
        ]);
    }
}
