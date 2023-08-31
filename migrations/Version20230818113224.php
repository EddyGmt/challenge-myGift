<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230818113224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gift ADD liste_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990DE85441D8 FOREIGN KEY (liste_id) REFERENCES liste (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A47C990DE85441D8 ON gift (liste_id)');
        $this->addSql('ALTER TABLE liste ADD user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE liste ADD CONSTRAINT FK_FCF22AF49D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FCF22AF49D86650F ON liste (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE liste DROP CONSTRAINT FK_FCF22AF49D86650F');
        $this->addSql('DROP INDEX IDX_FCF22AF49D86650F');
        $this->addSql('ALTER TABLE liste DROP user_id_id');
        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990DE85441D8');
        $this->addSql('DROP INDEX IDX_A47C990DE85441D8');
        $this->addSql('ALTER TABLE gift DROP liste_id');
    }
}
