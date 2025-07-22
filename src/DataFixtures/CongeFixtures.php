<?php
// src/DataFixtures/CongeFixtures.php
// src/DataFixtures/CongeFixtures.php
// src/DataFixtures/CongeFixtures.php
namespace App\DataFixtures;

use App\Entity\Conge;
use App\Entity\User;
use App\Enum\TypeConge;
use App\Enum\StatutDeDemande;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CongeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Get references to all users
        $jean = $this->getReference('user_jean', User::class);
        $claire = $this->getReference('user_claire', User::class);
        $alice = $this->getReference('user_alice', User::class);
        $thomas = $this->getReference('user_thomas', User::class);
        $sophie = $this->getReference('user_sophie', User::class);

        // --- Jean's Leaves (Development Team) ---
        $manager->persist($this->createLeave(
            $jean,
            '2025-08-01',
            '2025-08-10',
            TypeConge::ANNUEL,
            'Vacances d\'été',
            StatutDeDemande::ACCEPTE,
            '2025-06-15',
            '2025-06-20',
            'Manager Martin'
        ));

        $manager->persist($this->createLeave(
            $jean,
            '2025-09-01',
            '2025-09-05',
            TypeConge::ANNUEL,
            'Week-end prolongé',
            StatutDeDemande::EN_ATTENTE,
            '2025-07-01'
        ));

        $manager->persist($this->createLeave(
            $jean,
            '2025-12-20',
            '2026-01-05',
            TypeConge::ANNUEL,
            'Vacances de Noël',
            StatutDeDemande::EN_ATTENTE,
            '2025-10-15'
        ));

        // --- Claire's Leaves (Manager) ---
        $manager->persist($this->createLeave(
            $claire,
            '2025-10-10',
            '2025-10-15',
            TypeConge::EXCEPTIONNEL,
            'Formation professionnelle',
            StatutDeDemande::EN_ATTENTE,
            '2025-09-01'
        ));

        $manager->persist($this->createLeave(
            $claire,
            '2025-11-20',
            '2025-11-22',
            TypeConge::EXCEPTIONNEL,
            'Rendez-vous médical',
            StatutDeDemande::EN_ATTENTE,
            '2025-10-10'
        ));

        // --- Alice's Leaves (HR Team) ---
        $manager->persist($this->createLeave(
            $alice,
            '2025-08-15',
            '2025-08-22',
            TypeConge::ANNUEL,
            'Vacances en famille',
            StatutDeDemande::ACCEPTE,
            '2025-06-20',
            '2025-06-25',
            'Claire Dubois'
        ));

        $manager->persist($this->createLeave(
            $alice,
            '2025-09-10',
            '2025-09-12',
            TypeConge::EXCEPTIONNEL,
            'Déménagement',
            StatutDeDemande::EN_ATTENTE,
            '2025-08-01'
        ));

        $manager->persist($this->createLeave(
            $alice,
            '2025-10-05',
            '2025-10-10',
            TypeConge::ANNUEL,
            'Vacances d\'automne',
            StatutDeDemande::EN_ATTENTE,
            '2025-07-15'
        ));

        // --- Thomas's Leaves (HR Team) ---
        $manager->persist($this->createLeave(
            $thomas,
            '2025-07-20',
            '2025-07-25',
            TypeConge::ANNUEL,
            'Retraite des parents',
            StatutDeDemande::ACCEPTE,
            '2025-06-10',
            '2025-06-15',
            'Claire Dubois'
        ));

        $manager->persist($this->createLeave(
            $thomas,
            '2025-08-28',
            '2025-08-30',
            TypeConge::EXCEPTIONNEL,
            'Mariage d\'ami',
            StatutDeDemande::EN_ATTENTE,
            '2025-07-20'
        ));

        $manager->persist($this->createLeave(
            $thomas,
            '2025-11-15',
            '2025-11-20',
            TypeConge::ANNUEL,
            'Vacances au ski',
            StatutDeDemande::EN_ATTENTE,
            '2025-09-01'
        ));

        // --- Sophie's Leaves (HR Team) ---
        $manager->persist($this->createLeave(
            $sophie,
            '2025-09-15',
            '2025-09-20',
            TypeConge::ANNUEL,
            'Anniversaire de mariage',
            StatutDeDemande::ACCEPTE,
            '2025-07-01',
            '2025-07-05',
            'Claire Dubois'
        ));

        $manager->persist($this->createLeave(
            $sophie,
            '2025-10-25',
            '2025-10-27',
            TypeConge::EXCEPTIONNEL,
            'Congé parental',
            StatutDeDemande::EN_ATTENTE,
            '2025-09-10'
        ));

        $manager->persist($this->createLeave(
            $sophie,
            '2025-12-10',
            '2025-12-20',
            TypeConge::ANNUEL,
            'Vacances de fin d\'année',
            StatutDeDemande::EN_ATTENTE,
            '2025-10-01'
        ));

        $manager->flush();
    }

    private function createLeave(
        User $user,
        string $startDate,
        string $endDate,
        TypeConge $type,
        string $reason,
        StatutDeDemande $status,
        string $requestDate,
        ?string $processDate = null,
        ?string $processedBy = null
    ): Conge {
        $leave = new Conge();
        $leave->setDateDebut(new \DateTime($startDate))
            ->setDateFin(new \DateTime($endDate))
            ->setTypeConge($type)
            ->setMotif($reason)
            ->setStatut($status)
            ->setDateDemande(new \DateTime($requestDate))
            ->setUser($user);

        if ($processDate) {
            $leave->setDateTraitement(new \DateTime($processDate));
        }

        if ($processedBy) {
            $leave->setTraitePar($processedBy);
        }

        $user->addConge($leave);

        return $leave;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
