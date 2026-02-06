<?php

namespace App\DataFixtures;

use App\Entity\Leave;
use App\Entity\User;
use App\Enum\LeaveType;
use App\Enum\RequestStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LeaveFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $jean = $this->getReference(UserFixtures::USER_JEAN_REFERENCE, User::class);
        $alice = $this->getReference(UserFixtures::USER_ALICE_REFERENCE, User::class);
        $thomas = $this->getReference(UserFixtures::USER_THOMAS_REFERENCE, User::class);
        $sophie = $this->getReference(UserFixtures::USER_SOPHIE_REFERENCE, User::class);
        $claire = $this->getReference(UserFixtures::MANAGER_DEV_REFERENCE, User::class);

        // Jean's Leaves
        $manager->persist($this->createLeave(
            $jean,
            '2025-08-01',
            '2025-08-10',
            LeaveType::ANNUEL,
            'Vacances d\'été',
            RequestStatus::ACCEPTE,
            '2025-06-15',
            '2025-06-20',
            'Claire Dubois'
        ));

        $manager->persist($this->createLeave(
            $jean,
            '2025-09-01',
            '2025-09-05',
            LeaveType::ANNUEL,
            'Week-end prolongé',
            RequestStatus::EN_ATTENTE,
            '2025-07-01'
        ));

        // ... (other leave entries remain the same, just update the createLeave method below)
        // Add all other leave entries here following the same pattern

        $manager->flush();
    }

    private function createLeave(
        User $user,
        string $startDate,
        string $endDate,
        LeaveType $type,
        string $reason,
        RequestStatus $status,
        string $requestDate,
        ?string $processDate = null,
        ?string $processedBy = null
    ): Leave {
        $leave = new Leave();
        $leave->setStartDate(new \DateTime($startDate))  // Changed from setDateDebut
            ->setEndDate(new \DateTime($endDate))  // Changed from setDateFin
            ->setLeaveType($type)  // Changed from setTypeConge
            ->setReason($reason)  // Changed from setMotif
            ->setStatus($status)  // Changed from setStatut
            ->setRequestDate(new \DateTime($requestDate))  // Changed from setDateDemande
            ->setUser($user);

        if ($processDate) {
            $leave->setProcessDate(new \DateTime($processDate));  // Changed from setDateTraitement
        }

        if ($processedBy) {
            $leave->setProcessedBy($processedBy);  // Changed from setTraitePar
        }

        $user->addLeave($leave);  // Changed from addConge

        return $leave;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}