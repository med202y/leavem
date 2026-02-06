<?php
// src/DataFixtures/TeamFixtures.php
namespace App\DataFixtures;

use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture
{
    public const TEAM_ALPHA = 'team-alpha';
    public const TEAM_BETA  = 'team-beta';

    public function load(ObjectManager $manager): void
    {
        $alpha = new Team();
        $alpha->setName('Alpha')
            ->setDescription('Équipe Alpha, en charge du développement front-end');
        $manager->persist($alpha);
        // on garde une référence pour UserFixtures
        $this->addReference(self::TEAM_ALPHA, $alpha);

        $beta = new Team();
        $beta->setName('Beta')
            ->setDescription('Équipe Beta, en charge du back-end et de l’infra');
        $manager->persist($beta);
        $this->addReference(self::TEAM_BETA, $beta);

        $manager->flush();
    }
}
