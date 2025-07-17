<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717140033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD cin VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD cnss VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD salaire_m INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD qualificationsprofessionnelles VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD date_douverture_droit_au_conge TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD nbr_de_jours_travaille INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD jourdeconge_travail DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD observations VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP cin');
        $this->addSql('ALTER TABLE "user" DROP cnss');
        $this->addSql('ALTER TABLE "user" DROP salaire_m');
        $this->addSql('ALTER TABLE "user" DROP qualificationsprofessionnelles');
        $this->addSql('ALTER TABLE "user" DROP date_douverture_droit_au_conge');
        $this->addSql('ALTER TABLE "user" DROP nbr_de_jours_travaille');
        $this->addSql('ALTER TABLE "user" DROP jourdeconge_travail');
        $this->addSql('ALTER TABLE "user" DROP observations');
    }
}
