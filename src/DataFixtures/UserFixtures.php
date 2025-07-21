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
            ->setDescription('Équipe Alpha, en charge du développement front-end');;
        $manager->persist($team1);

        $team2 = new Team();
        $team2->setName('HR Team')
            ->setDescription('Équipe Alpha, en charge du développement front-end');;
        $manager->persist($team2);

        // User 1 - Jean
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
        $manager->persist($user1);
        $this->addReference('user_jean', $user1);

        // User 2 - Claire
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
        $manager->persist($user2);
        $this->addReference('user_claire', $user2);

        $manager->flush();
    }
}
