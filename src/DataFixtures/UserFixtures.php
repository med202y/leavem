<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Team;
use App\Enum\Role;
use App\Enum\Departement;
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
        // Create teams
        $team1 = new Team();
        $team1->setName('Development Team')
            ->setDescription('Équipe Alpha, en charge du développement front-end');
        $manager->persist($team1);

        $team2 = new Team();
        $team2->setName('HR Team')
            ->setDescription('Équipe Bravo, en charge des ressources humaines');
        $manager->persist($team2);

        // User 1 - Jean (Employee) - Team1
        $user1 = new User();
        $user1->setNom('Dupont');
        $user1->setPrenom('Jean');
        $user1->setEmail('jean.dupont@example.com');
        $password = $this->hasher->hashPassword($user1, 'password');
        $user1->setMotDePasse($password);
        $user1->setRole([Role::EMPLOYEE]);
        $user1->setSoldeConge(20);
        $user1->setDepartement(Departement::SYMFONY);
        $user1->setTeam($team1);
        $user1->setDateDembauche(new \DateTime('2022-03-15'));
        $manager->persist($user1);
        $this->addReference('user_jean', $user1);

        // User 2 - Claire (Manager) - Team2
        $user2 = new User();
        $user2->setNom('Dubois');
        $user2->setPrenom('Claire');
        $user2->setEmail('claire.dubois@example.com');
        $password = $this->hasher->hashPassword($user2, 'password');
        $user2->setMotDePasse($password);
        $user2->setRole([Role::MANAGER]);
        $user2->setSoldeConge(15);
        $user2->setDepartement(Departement::INTEGRATEUR);
        $user2->setTeam($team2);
        $user2->setDateDembauche(new \DateTime('2021-11-01'));
        $manager->persist($user2);
        $this->addReference('user_claire', $user2);

        // Employee 1 - Alice
        $alice = new User();
        $alice->setNom('Martin');
        $alice->setPrenom('Alice');
        $alice->setEmail('alice.martin@example.com');
        $password = $this->hasher->hashPassword($alice, 'password');
        $alice->setMotDePasse($password);
        $alice->setRole([Role::EMPLOYEE]);
        $alice->setSoldeConge(18);
        $alice->setDepartement(Departement::INTEGRATEUR);
        $alice->setTeam($team2);
        $alice->setDateDembauche(new \DateTime('2023-01-10'));
        $manager->persist($alice);
        $this->addReference('user_alice', $alice);

        // Employee 2 - Thomas
        $thomas = new User();
        $thomas->setNom('Lefevre');
        $thomas->setPrenom('Thomas');
        $thomas->setEmail('thomas.lefevre@example.com');
        $password = $this->hasher->hashPassword($thomas, 'password');
        $thomas->setMotDePasse($password);
        $thomas->setRole([Role::EMPLOYEE]);
        $thomas->setSoldeConge(22);
        $thomas->setDepartement(Departement::INTEGRATEUR);
        $thomas->setTeam($team2);
        $thomas->setDateDembauche(new \DateTime('2022-06-05'));
        $manager->persist($thomas);
        $this->addReference('user_thomas', $thomas);

        // Employee 3 - Sophie
        $sophie = new User();
        $sophie->setNom('Petit');
        $sophie->setPrenom('Sophie');
        $sophie->setEmail('sophie.petit@example.com');
        $password = $this->hasher->hashPassword($sophie, 'password');
        $sophie->setMotDePasse($password);
        $sophie->setRole([Role::EMPLOYEE]);
        $sophie->setSoldeConge(15);
        $sophie->setDepartement(Departement::INTEGRATEUR);
        $sophie->setTeam($team2);
        $sophie->setDateDembauche(new \DateTime('2023-07-01'));
        $manager->persist($sophie);
        $this->addReference('user_sophie', $sophie);

        // Big Admin
        $bigAdmin = new User();
        $bigAdmin->setNom('Root');
        $bigAdmin->setPrenom('Admin');
        $bigAdmin->setEmail('admin@example.com');
        $password = $this->hasher->hashPassword($bigAdmin, 'adminpass');
        $bigAdmin->setMotDePasse($password);
        $bigAdmin->setRole([Role::BIGADMIN]);
        $bigAdmin->setSoldeConge(99);
        $bigAdmin->setDepartement(Departement::SYMFONY);
        $bigAdmin->setTeam($team1);
        $bigAdmin->setDateDembauche(new \DateTime('2020-01-01'));
        $manager->persist($bigAdmin);
        $this->addReference('user_admin', $bigAdmin);

        $manager->flush();
    }
}

