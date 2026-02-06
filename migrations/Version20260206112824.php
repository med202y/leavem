<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206112824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE leave (id SERIAL NOT NULL, user_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, leave_type VARCHAR(255) NOT NULL, reason TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, leave_request_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, process_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, processed_by VARCHAR(255) DEFAULT NULL, proof VARCHAR(255) DEFAULT NULL, refusal_reason TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9BB080D0A76ED395 ON leave (user_id)');
        $this->addSql('CREATE TABLE manager (id INT NOT NULL, equipe VARCHAR(255) DEFAULT NULL, typedevalidation VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "notification" (id SERIAL NOT NULL, recipient_id INT NOT NULL, title VARCHAR(255) NOT NULL, message VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CAE92F8F78 ON "notification" (recipient_id)');
        $this->addSql('COMMENT ON COLUMN "notification".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "team" (id SERIAL NOT NULL, manager_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C4E0A61F783E3463 ON "team" (manager_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, team_id INT DEFAULT NULL, direct_manager_id INT DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles TEXT NOT NULL, leave_balance DOUBLE PRECISION NOT NULL, department VARCHAR(255) NOT NULL, notes VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, hire_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, user_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D93D649296CD8AE ON "user" (team_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6496F1BF1B0 ON "user" (direct_manager_id)');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE leave ADD CONSTRAINT FK_9BB080D0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE manager ADD CONSTRAINT FK_FA2425B9BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "notification" ADD CONSTRAINT FK_BF5476CAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "team" ADD CONSTRAINT FK_C4E0A61F783E3463 FOREIGN KEY (manager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES "team" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6496F1BF1B0 FOREIGN KEY (direct_manager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE leave DROP CONSTRAINT FK_9BB080D0A76ED395');
        $this->addSql('ALTER TABLE manager DROP CONSTRAINT FK_FA2425B9BF396750');
        $this->addSql('ALTER TABLE "notification" DROP CONSTRAINT FK_BF5476CAE92F8F78');
        $this->addSql('ALTER TABLE "team" DROP CONSTRAINT FK_C4E0A61F783E3463');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649296CD8AE');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6496F1BF1B0');
        $this->addSql('DROP TABLE leave');
        $this->addSql('DROP TABLE manager');
        $this->addSql('DROP TABLE "notification"');
        $this->addSql('DROP TABLE "team"');
        $this->addSql('DROP TABLE "user"');
    }
}
