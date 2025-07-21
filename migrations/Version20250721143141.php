<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721143141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id SERIAL NOT NULL, recipient_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, message VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CAE92F8F78 ON notification (recipient_id)');
        $this->addSql('COMMENT ON COLUMN notification.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE team (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conge ADD justificatif VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP cin');
        $this->addSql('ALTER TABLE "user" DROP cnss');
        $this->addSql('ALTER TABLE "user" DROP salaire_m');
        $this->addSql('ALTER TABLE "user" DROP qualificationsprofessionnelles');
        $this->addSql('ALTER TABLE "user" DROP date_douverture_droit_au_conge');
        $this->addSql('ALTER TABLE "user" DROP nbr_de_jours_travaille');
        $this->addSql('ALTER TABLE "user" DROP jourdeconge_travail');
        $this->addSql('ALTER TABLE "user" DROP date_dembauche');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649296CD8AE ON "user" (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649296CD8AE');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAE92F8F78');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE team');
        $this->addSql('ALTER TABLE conge DROP justificatif');
        $this->addSql('DROP INDEX IDX_8D93D649296CD8AE');
        $this->addSql('ALTER TABLE "user" ADD cin VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD cnss VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD salaire_m INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD qualificationsprofessionnelles VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD date_douverture_droit_au_conge TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD nbr_de_jours_travaille INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD jourdeconge_travail DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD date_dembauche DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP team_id');
    }
}
