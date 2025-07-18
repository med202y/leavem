<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718135459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conge ALTER motif DROP NOT NULL');
        $this->addSql('ALTER TABLE conge ALTER date_traitement DROP NOT NULL');
        $this->addSql('ALTER TABLE conge ALTER traite_par DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD date_dembauche DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conge ALTER motif SET NOT NULL');
        $this->addSql('ALTER TABLE conge ALTER date_traitement SET NOT NULL');
        $this->addSql('ALTER TABLE conge ALTER traite_par SET NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP date_dembauche');
    }
}
