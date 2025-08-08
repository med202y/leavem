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
        // Create managers first
        $managerDev = new User();
        $managerDev->setLastName('Dubois')  // Changed from setNom
            ->setFirstName('Claire')  // Changed from setPrenom
            ->setEmail('claire.dubois@example.com')
            ->setPassword($this->hasher->hashPassword($managerDev, 'password'))  // Changed from setMotDePasse
            ->setRolesArray([Role::MANAGER])  // Changed from setRole
            ->setLeaveBalance(15)  // Changed from setSoldeConge
            ->setDepartment(Departement::INTEGRATEUR)  // Changed from setDepartement
            ->setHireDate(new \DateTime('2021-11-01'));  // Changed from setDateDembauche
        $manager->persist($managerDev);
        $this->addReference(self::MANAGER_DEV_REFERENCE, $managerDev);

        $managerHR = new User();
        $managerHR->setLastName('Smith')
            ->setFirstName('John')
            ->setEmail('john.smith@example.com')
            ->setPassword($this->hasher->hashPassword($managerHR, 'password'))
            ->setRolesArray([Role::MANAGER])
            ->setLeaveBalance(15)
            ->setDepartment(Departement::SYMFONY)
            ->setHireDate(new \DateTime('2021-08-20'));
        $manager->persist($managerHR);
        $this->addReference(self::MANAGER_HR_REFERENCE, $managerHR);

        // Create teams
        $team1 = new Team();
        $team1->setName('Development Team')
            ->setDescription('Équipe Alpha, en charge du développement front-end')
            ->setManager($managerDev);
        $manager->persist($team1);
        $this->addReference(self::TEAM_DEV_REFERENCE, $team1);

        $team2 = new Team();
        $team2->setName('HR Team')
            ->setDescription('Équipe Bravo, en charge des ressources humaines')
            ->setManager($managerHR);
        $manager->persist($team2);
        $this->addReference(self::TEAM_HR_REFERENCE, $team2);

        // Assign managers to their teams
        $managerDev->setTeam($team1);
        $managerHR->setTeam($team2);

        // Employees of team1
        $user1 = new User();
        $user1->setLastName('Dupont')
            ->setFirstName('Jean')
            ->setEmail('jean.dupont@example.com')
            ->setPassword($this->hasher->hashPassword($user1, 'password'))
            ->setRolesArray([Role::EMPLOYEE])
            ->setLeaveBalance(20)
            ->setDepartment(Departement::SYMFONY)
            ->setTeam($team1)
            ->setHireDate(new \DateTime('2022-03-15'));
        $manager->persist($user1);
        $this->addReference(self::USER_JEAN_REFERENCE, $user1);

        // Employees of team2
        $alice = new User();
        $alice->setLastName('Martin')
            ->setFirstName('Alice')
            ->setEmail('alice.martin@example.com')
            ->setPassword($this->hasher->hashPassword($alice, 'password'))
            ->setRolesArray([Role::EMPLOYEE])
            ->setLeaveBalance(18)
            ->setDepartment(Departement::INTEGRATEUR)
            ->setTeam($team2)
            ->setHireDate(new \DateTime('2023-01-10'));
        $manager->persist($alice);
        $this->addReference(self::USER_ALICE_REFERENCE, $alice);

        $thomas = new User();
        $thomas->setLastName('Lefevre')
            ->setFirstName('Thomas')
            ->setEmail('thomas.lefevre@example.com')
            ->setPassword($this->hasher->hashPassword($thomas, 'password'))
            ->setRolesArray([Role::EMPLOYEE])
            ->setLeaveBalance(22)
            ->setDepartment(Departement::INTEGRATEUR)
            ->setTeam($team2)
            ->setHireDate(new \DateTime('2022-06-05'));
        $manager->persist($thomas);
        $this->addReference(self::USER_THOMAS_REFERENCE, $thomas);

        $sophie = new User();
        $sophie->setLastName('Petit')
            ->setFirstName('Sophie')
            ->setEmail('sophie.petit@example.com')
            ->setPassword($this->hasher->hashPassword($sophie, 'password'))
            ->setRolesArray([Role::EMPLOYEE])
            ->setLeaveBalance(15)
            ->setDepartment(Departement::INTEGRATEUR)
            ->setTeam($team2)
            ->setHireDate(new \DateTime('2023-07-01'));
        $manager->persist($sophie);
        $this->addReference(self::USER_SOPHIE_REFERENCE, $sophie);

        // Admin
        $bigAdmin = new User();
        $bigAdmin->setLastName('Root')
            ->setFirstName('Admin')
            ->setEmail('admin@example.com')
            ->setPassword($this->hasher->hashPassword($bigAdmin, 'adminpass'))
            ->setRolesArray([Role::BIGADMIN])
            ->setLeaveBalance(99)
            ->setDepartment(Departement::SYMFONY)
            ->setTeam($team1)
            ->setHireDate(new \DateTime('2020-01-01'));
        $manager->persist($bigAdmin);
        $this->addReference(self::ADMIN_REFERENCE, $bigAdmin);

        $manager->flush();
    }
}