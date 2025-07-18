<?php
// src/DataFixtures/UserFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\departement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        $user1 = new User();
        $user1->setNom('Dupont');
        $user1->setPrenom('Jean');
        $user1->setEmail('jean.dupont@example.com');
        $user1->setRole([Role::EMPLOYEE]);
        $user1->setDepartement(Departement::INTEGRATEUR);
        $user1->setSoldeConge(15.5);
        $user1->setCIN('A123456');
        $user1->setCNSS('J123456789');
        $user1->setSalaireM(15000);
        $user1->setQualificationsprofessionnelles('Bac+5 Informatique');
        $user1->setDateDouvertureDroitAuConge(new \DateTime('2022-01-15'));
        $user1->setNbrDeJoursTravaille(220);
        $user1->setJourdecongeTravail(1.83);
        $user1->setObservations('Excellent employé');
        $user1->setChemin('photo_jean.png'); // Example image path
        $user1->setDateDembauche(new \DateTime('2020-03-01')); // New field

        $hashedPassword1 = $this->hasher->hashPassword($user1, 'password123');
        $user1->setMotDePasse($hashedPassword1);

        $manager->persist($user1);

        $user2 = new User();
        $user2->setNom('Martin');
        $user2->setPrenom('Claire');
        $user2->setEmail('claire.martin@example.com');
        $user2->setRole([Role::MANAGER]);
        $user2->setDepartement(Departement::SYMFONY);
        $user2->setSoldeConge(20);
        $user2->setCIN('B789012');
        $user2->setCNSS('M987654321');
        $user2->setSalaireM(20000);
        $user2->setQualificationsprofessionnelles('Bac+5 Management');
        $user2->setDateDouvertureDroitAuConge(new \DateTime('2021-06-10'));
        $user2->setNbrDeJoursTravaille(250);
        $user2->setJourdecongeTravail(2.08);
        $user2->setObservations('Manager de département');
        $user2->setChemin('photo_claire.png'); // Example image path
        $user2->setDateDembauche(new \DateTime('2019-09-15')); // New field

        $hashedPassword2 = $this->hasher->hashPassword($user2, 'securepass');
        $user2->setMotDePasse($hashedPassword2);

        $manager->persist($user2);

        // You can add more users here...

        $manager->flush();
    }
}

