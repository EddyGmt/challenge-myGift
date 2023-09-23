<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230923191303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gift ADD reservation_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D3C3B4EF0 FOREIGN KEY (reservation_id_id) REFERENCES reservation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A47C990D3C3B4EF0 ON gift (reservation_id_id)');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT fk_42c849556a6cceec');
        $this->addSql('DROP INDEX uniq_42c849556a6cceec');
        $this->addSql('ALTER TABLE reservation DROP gift_id_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE reservation ADD gift_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT fk_42c849556a6cceec FOREIGN KEY (gift_id_id) REFERENCES gift (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_42c849556a6cceec ON reservation (gift_id_id)');
        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990D3C3B4EF0');
        $this->addSql('DROP INDEX IDX_A47C990D3C3B4EF0');
        $this->addSql('ALTER TABLE gift DROP reservation_id_id');
    }
}
