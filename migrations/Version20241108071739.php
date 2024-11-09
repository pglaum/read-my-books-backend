<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108071739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add SavedBook entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE saved_book (id SERIAL NOT NULL, volume_id_id INT DEFAULT NULL, user_id TEXT NOT NULL, owned_count INT DEFAULT NULL, page_progress INT DEFAULT NULL, page_count INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C5DA5124D4BFE7A1 ON saved_book (volume_id_id)');
        $this->addSql('ALTER TABLE saved_book ADD CONSTRAINT FK_C5DA5124D4BFE7A1 FOREIGN KEY (volume_id_id) REFERENCES google_volume (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE saved_book DROP CONSTRAINT FK_C5DA5124D4BFE7A1');
        $this->addSql('DROP TABLE saved_book');
    }
}
