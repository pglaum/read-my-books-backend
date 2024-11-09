<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241109120602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename volume_id to volume in saved_book';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE saved_book DROP CONSTRAINT fk_c5da5124d4bfe7a1');
        $this->addSql('DROP INDEX idx_c5da5124d4bfe7a1');
        $this->addSql('ALTER TABLE saved_book RENAME COLUMN volume_id_id TO volume_id');
        $this->addSql('ALTER TABLE saved_book ADD CONSTRAINT FK_C5DA51248FD80EEA FOREIGN KEY (volume_id) REFERENCES google_volume (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C5DA51248FD80EEA ON saved_book (volume_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE saved_book DROP CONSTRAINT FK_C5DA51248FD80EEA');
        $this->addSql('DROP INDEX IDX_C5DA51248FD80EEA');
        $this->addSql('ALTER TABLE saved_book RENAME COLUMN volume_id TO volume_id_id');
        $this->addSql('ALTER TABLE saved_book ADD CONSTRAINT fk_c5da5124d4bfe7a1 FOREIGN KEY (volume_id_id) REFERENCES google_volume (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c5da5124d4bfe7a1 ON saved_book (volume_id_id)');
    }
}
