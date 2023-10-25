<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231025090243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE reservation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE gift (id INT NOT NULL, liste_id INT DEFAULT NULL, reservation_id_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, image VARCHAR(255) DEFAULT NULL, link VARCHAR(255) NOT NULL, is_reserved BOOLEAN DEFAULT NULL, reserved_by VARCHAR(255) DEFAULT NULL, email_reservation VARCHAR(255) DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A47C990DE85441D8 ON gift (liste_id)');
        $this->addSql('CREATE INDEX IDX_A47C990D3C3B4EF0 ON gift (reservation_id_id)');
        $this->addSql('CREATE TABLE liste (id INT NOT NULL, user_id_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, cover VARCHAR(255) DEFAULT NULL, theme VARCHAR(50) NOT NULL, status INT NOT NULL, password VARCHAR(50) DEFAULT NULL, date_ouveture DATE NOT NULL, date_fin_ouverture DATE DEFAULT NULL, is_archived BOOLEAN DEFAULT NULL, is_private BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FCF22AF49D86650F ON liste (user_id_id)');
        $this->addSql('CREATE TABLE reservation (id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, address VARCHAR(100) DEFAULT NULL, zipcode INT DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, username VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990DE85441D8 FOREIGN KEY (liste_id) REFERENCES liste (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D3C3B4EF0 FOREIGN KEY (reservation_id_id) REFERENCES reservation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE liste ADD CONSTRAINT FK_FCF22AF49D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE reservation_id_seq CASCADE');
        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990DE85441D8');
        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990D3C3B4EF0');
        $this->addSql('ALTER TABLE liste DROP CONSTRAINT FK_FCF22AF49D86650F');
        $this->addSql('DROP TABLE gift');
        $this->addSql('DROP TABLE liste');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
