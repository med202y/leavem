<?php
// src/DataFixtures/CongeFixtures.php

namespace App\DataFixtures;

use App\Entity\Conge;
use App\Enum\typeconge;
use App\Enum\statutdedemande;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CongeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $conge1 = new Conge();
        $conge1->setDateDebut(new \DateTime('2025-08-01'));
        $conge1->setDateFin(new \DateTime('2025-08-10'));
        $conge1->setTypeConge(typeconge::ANNUEL); // example enum value
        $conge1->setMotif('Vacances d\'été');
        $conge1->setStatut(statutdedemande::APPROUVEE); // example enum value
        $conge1->setDateDemande(new \DateTime('2025-06-15'));
        $conge1->setDateTraitement(new \DateTime('2025-06-20'));
        $conge1->setTraitePar('Manager Martin');

        $manager->persist($conge1);

        $conge2 = new Conge();
        $conge2->setDateDebut(new \DateTime('2025-09-05'));
        $conge2->setDateFin(new \DateTime('2025-09-07'));
        $conge2->setTypeConge(typeconge::EXCEPTIONNEL);
        $conge2->setMotif('Mariage');
        $conge2->setStatut(statutdedemande::EN_ATTENTE);
        $conge2->setDateDemande(new \DateTime('2025-07-01'));
        $conge2->setDateTraitement(new \DateTime('2025-06-20'));
        $conge2->setTraitePar('');

        $manager->persist($conge2);

        $manager->flush();
    }
}
