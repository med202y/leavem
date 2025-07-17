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
        $user1->setRole([Role::EMPLOYEE]); // Role is an array of Role enums
        $user1->setDepartement(Departement::integrateur);
        $user1->setSoldeConge(15.5); // 15.5 days of leave left

        $hashedPassword1 = $this->hasher->hashPassword($user1, 'password123');
        $user1->setMotDePasse($hashedPassword1);

        $manager->persist($user1);


        $user2 = new User();
        $user2->setNom('Martin');
        $user2->setPrenom('Claire');
        $user2->setEmail('claire.martin@example.com');
        $user2->setRole([Role::MANAGER]);
        $user2->setDepartement(Departement::symfony);
        $user2->setSoldeConge(20);

        $hashedPassword2 = $this->hasher->hashPassword($user2, 'securepass');
        $user2->setMotDePasse($hashedPassword2);

        $manager->persist($user2);

        // You can add more users here...

        $manager->flush();
    }
}

