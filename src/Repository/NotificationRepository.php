<?php

namespace App\Repository;
use App\Entity\Leave;
use App\Entity\User;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }
    public function createLeaveModifiedNotifications(Leave $leave, User $user): void
    {
        $em = $this->getEntityManager();

        // Create notification for employee
        $notifEmp = new Notification();
        $notifEmp->setTitle('Demande de congé modifiée');
        $notifEmp->setMessage("Votre demande de congé a été modifiée. Nouvelle période : " .
            $leave->getStartDate()->format('d/m/Y') . " au " .
            $leave->getEndDate()->format('d/m/Y') . ".");
        $notifEmp->setRecipient($user);
        $em->persist($notifEmp);

        // Create notification for manager if exists
        $manager = $user->getManager();
        if ($manager) {
            $notifMan = new Notification();
            $notifMan->setTitle('Action : Congé modifié');
            $notifMan->setMessage($user->getFirstName() . " " . $user->getLastName() .
                " a modifié sa demande de congé. Nouvelle période : " .
                $leave->getStartDate()->format('d/m/Y') . " au " .
                $leave->getEndDate()->format('d/m/Y') . ".");
            $notifMan->setRecipient($manager);
            $em->persist($notifMan);
        }


    }
    public function createLeaveCancellationNotifications(Leave $leave, User $user): void
    {
        $em = $this->getEntityManager();

        $notifEmp = new Notification();
                $notifEmp->setTitle('Demande d\'annulation envoyée');
                $notifEmp->setMessage("Votre demande d'annulation du congé du " .
                    $leave->getStartDate()->format('d/m/Y') . " au " .
                    $leave->getEndDate()->format('d/m/Y') . " est en attente de validation.");
                $notifEmp->setRecipient($user);
                $manager = $user->getManager();
                if ($manager) {
                    $notifMan = new Notification();
                    $notifMan->setTitle('Action requise : Annulation à valider');
                    $notifMan->setMessage($user->getFirstName() . " " . $user->getLastName() .
                        " a demandé l'annulation de son congé (du " .
                        $leave->getStartDate()->format('d/m/Y') . " au " .
                        $leave->getEndDate()->format('d/m/Y') . ").");
                    $notifMan->setRecipient($manager);
                    $em->persist($notifMan);
                }

                $em->persist($notifEmp);
                $em->flush();

        }
        public function createRequestLeaveNotification(Leave $leave, User $user): void
        {
            $em = $this->getEntityManager();
                        // Notification for the user
            $notification = new Notification();
            $notification->setTitle('Nouvelle demande de congé');
            $notification->setMessage('Votre demande de congé du ' .
                $leave->getStartDate()->format('d/m/Y') . ' au ' . // Changed from getDateDebut
                $leave->getEndDate()->format('d/m/Y') . ' a été enregistrée.'); // Changed from getDateFin
            $notification->setRecipient($user);
            $em->persist($notification);

            // Notification for the manager
            $manager = $user->getManager();
            if ($manager instanceof User) {
                $notifManager = new Notification();
                $notifManager->setTitle('Nouvelle demande de congé à valider');
                $notifManager->setMessage(
                    $user->getFirstName() . ' ' . $user->getLastName() .
                    ' a soumis une nouvelle demande de congé du ' .
                    $leave->getStartDate()->format('d/m/Y') . ' au ' . // Changed from getDateDebut
                    $leave->getEndDate()->format('d/m/Y') . '.' // Changed from getDateFin
                );
                $notifManager->setRecipient($manager);
                $em->persist($notifManager);
            }

            $em->flush();
        }
        public function profileUpdateNotification(User $user): void
        {
            $em = $this->getEntityManager();
            $notification = new Notification();
            $notification->setTitle('Profil mis a jour');
            $notification->setMessage("Votre mot de passe a été changé avec succes");
            $notification->setRecipient($user);

            $em->persist($notification);
            $em->flush();

            $em->persist($user);
            $em->flush();
        }

    public function createAndPersistNotification(
        User $recipient,
        string $title,
        string $message,
        bool $persist = true
    ): Notification {
        $notification = new Notification();
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setRecipient($recipient);

        if ($persist) {
            $this->getEntityManager()->persist($notification);
        }

        return $notification;
    }
//    /**
//     * @return Notification[] Returns an array of Notification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Notification
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
