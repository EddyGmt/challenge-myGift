<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230818103159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD roles JSON NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD username VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER firstname TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" ALTER lastname TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE "user" ALTER password TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ALTER address TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" ALTER city TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" ALTER country TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN is_admin TO is_active');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('ALTER TABLE "user" DROP roles');
        $this->addSql('ALTER TABLE "user" DROP username');
        $this->addSql('ALTER TABLE "user" ALTER email TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER password TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER firstname TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER lastname TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER address TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER city TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" ALTER country TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN is_active TO is_admin');
    }
}
