<?php
// src/DataFixtures/CongeFixtures.php
namespace App\DataFixtures;

use App\Entity\Conge;
use App\Entity\User;               // ← IMPORT
use App\Enum\typeconge;
use App\Enum\statutdedemande;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CongeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // On fournit désormais 2 arguments : le nom + la classe
        /** @var User $user1 */
        $user1 = $this->getReference('user_jean', User::class);
        /** @var User $user2 */
        $user2 = $this->getReference('user_claire', User::class);

        // --- Congé 1 ---
        $conge1 = (new Conge())
            ->setDateDebut(new \DateTime('2025-08-01'))
            ->setDateFin(new \DateTime('2025-08-10'))
            ->setTypeConge(typeconge::ANNUEL)
            ->setMotif('Vacances d\'été')
            ->setStatut(statutdedemande::APPROUVEE)
            ->setDateDemande(new \DateTime('2025-06-15'))
            ->setDateTraitement(new \DateTime('2025-06-20'))
            ->setTraitePar('Manager Martin')
            ->setUser($user1)
        ;
        $user1->addConge($conge1);
        $manager->persist($conge1);

        // --- Congé 2 ---
        $conge2 = (new Conge())
            ->setDateDebut(new \DateTime('2025-09-05'))
            ->setDateFin(new \DateTime('2025-09-07'))
            ->setTypeConge(typeconge::EXCEPTIONNEL)
            ->setMotif('Mariage')
            ->setStatut(statutdedemande::EN_ATTENTE)
            ->setDateDemande(new \DateTime('2025-07-01'))
            ->setUser($user2)
        ;
        $user2->addConge($conge2);
        $manager->persist($conge2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
