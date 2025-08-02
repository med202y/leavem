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
    // Define constants for references
    public const USER_JEAN_REFERENCE = 'user_jean';
    public const USER_ALICE_REFERENCE = 'user_alice';
    public const USER_THOMAS_REFERENCE = 'user_thomas';
    public const USER_SOPHIE_REFERENCE = 'user_sophie';
    public const MANAGER_DEV_REFERENCE = 'manager_dev';
    public const MANAGER_HR_REFERENCE = 'manager_hr';
    public const ADMIN_REFERENCE = 'admin';
    public const TEAM_DEV_REFERENCE = 'team_dev';
    public const TEAM_HR_REFERENCE = 'team_hr';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create managers first (because teams need them)
        $managerDev = new User();
        $managerDev->setNom('Dubois');
        $managerDev->setPrenom('Claire');
        $managerDev->setEmail('claire.dubois@example.com');
        $managerDev->setMotDePasse($this->hasher->hashPassword($managerDev, 'password'));
        $managerDev->setRole([Role::MANAGER]);
        $managerDev->setSoldeConge(15);
        $managerDev->setDepartement(Departement::INTEGRATEUR);
        $managerDev->setDateDembauche(new \DateTime('2021-11-01'));
        $manager->persist($managerDev);
        // Add reference for manager
        $this->addReference(self::MANAGER_DEV_REFERENCE, $managerDev);

        $managerHR = new User();
        $managerHR->setNom('Smith');
        $managerHR->setPrenom('John');
        $managerHR->setEmail('john.smith@example.com');
        $managerHR->setMotDePasse($this->hasher->hashPassword($managerHR, 'password'));
        $managerHR->setRole([Role::MANAGER]);
        $managerHR->setSoldeConge(15);
        $managerHR->setDepartement(Departement::SYMFONY);
        $managerHR->setDateDembauche(new \DateTime('2021-08-20'));
        $manager->persist($managerHR);
        // Add reference for manager
        $this->addReference(self::MANAGER_HR_REFERENCE, $managerHR);

        // Create teams and assign managers
        $team1 = new Team();
        $team1->setName('Development Team')
            ->setDescription('Équipe Alpha, en charge du développement front-end')
            ->setManager($managerDev);
        $manager->persist($team1);
        // Add reference for team
        $this->addReference(self::TEAM_DEV_REFERENCE, $team1);

        $team2 = new Team();
        $team2->setName('HR Team')
            ->setDescription('Équipe Bravo, en charge des ressources humaines')
            ->setManager($managerHR);
        $manager->persist($team2);
        // Add reference for team
        $this->addReference(self::TEAM_HR_REFERENCE, $team2);

        // Assign managers to their teams
        $managerDev->setTeam($team1);
        $managerHR->setTeam($team2);

        // Employees of team1
        $user1 = new User();
        $user1->setNom('Dupont')
            ->setPrenom('Jean')
            ->setEmail('jean.dupont@example.com')
            ->setMotDePasse($this->hasher->hashPassword($user1, 'password'))
            ->setRole([Role::EMPLOYEE])
            ->setSoldeConge(20)
            ->setDepartement(Departement::SYMFONY)
            ->setTeam($team1)
            ->setDateDembauche(new \DateTime('2022-03-15'));
        $manager->persist($user1);
        // Add reference for Jean - this was missing!
        $this->addReference(self::USER_JEAN_REFERENCE, $user1);

        // Employees of team2
        $alice = new User();
        $alice->setNom('Martin')
            ->setPrenom('Alice')
            ->setEmail('alice.martin@example.com')
            ->setMotDePasse($this->hasher->hashPassword($alice, 'password'))
            ->setRole([Role::EMPLOYEE])
            ->setSoldeConge(18)
            ->setDepartement(Departement::INTEGRATEUR)
            ->setTeam($team2)
            ->setDateDembauche(new \DateTime('2023-01-10'));
        $manager->persist($alice);
        // Add reference for Alice
        $this->addReference(self::USER_ALICE_REFERENCE, $alice);

        $thomas = new User();
        $thomas->setNom('Lefevre')
            ->setPrenom('Thomas')
            ->setEmail('thomas.lefevre@example.com')
            ->setMotDePasse($this->hasher->hashPassword($thomas, 'password'))
            ->setRole([Role::EMPLOYEE])
            ->setSoldeConge(22)
            ->setDepartement(Departement::INTEGRATEUR)
            ->setTeam($team2)
            ->setDateDembauche(new \DateTime('2022-06-05'));
        $manager->persist($thomas);
        // Add reference for Thomas
        $this->addReference(self::USER_THOMAS_REFERENCE, $thomas);

        $sophie = new User();
        $sophie->setNom('Petit')
            ->setPrenom('Sophie')
            ->setEmail('sophie.petit@example.com')
            ->setMotDePasse($this->hasher->hashPassword($sophie, 'password'))
            ->setRole([Role::EMPLOYEE])
            ->setSoldeConge(15)
            ->setDepartement(Departement::INTEGRATEUR)
            ->setTeam($team2)
            ->setDateDembauche(new \DateTime('2023-07-01'));
        $manager->persist($sophie);
        // Add reference for Sophie
        $this->addReference(self::USER_SOPHIE_REFERENCE, $sophie);

        // Big Admin (no team required, but can belong to one)
        $bigAdmin = new User();
        $bigAdmin->setNom('Root')
            ->setPrenom('Admin')
            ->setEmail('admin@example.com')
            ->setMotDePasse($this->hasher->hashPassword($bigAdmin, 'adminpass'))
            ->setRole([Role::BIGADMIN])
            ->setSoldeConge(99)
            ->setDepartement(Departement::SYMFONY)
            ->setTeam($team1)
            ->setDateDembauche(new \DateTime('2020-01-01'));
        $manager->persist($bigAdmin);
        // Add reference for Admin
        $this->addReference(self::ADMIN_REFERENCE, $bigAdmin);

        $manager->flush();
    }
}

