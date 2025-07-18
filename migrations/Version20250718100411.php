<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718100411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD chemin VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER cin SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER cnss SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER salaire_m SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER qualificationsprofessionnelles SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER date_douverture_droit_au_conge SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER nbr_de_jours_travaille SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER jourdeconge_travail SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP chemin');
        $this->addSql('ALTER TABLE "user" ALTER cin DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER cnss DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER salaire_m DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER qualificationsprofessionnelles DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER date_douverture_droit_au_conge DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER nbr_de_jours_travaille DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER jourdeconge_travail DROP NOT NULL');
    }
}
