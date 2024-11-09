<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241109115307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add book event table and add columns to google volume and saved book';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_event (id SERIAL NOT NULL, saved_book_id INT DEFAULT NULL, event VARCHAR(255) NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_356C1FDE9CA37C16 ON book_event (saved_book_id)');
        $this->addSql('ALTER TABLE book_event ADD CONSTRAINT FK_356C1FDE9CA37C16 FOREIGN KEY (saved_book_id) REFERENCES saved_book (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE google_volume ADD title TEXT NOT NULL');
        $this->addSql('ALTER TABLE google_volume ADD authors JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD categories JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD thumbnail TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD image TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD published_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD publisher TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD subtitle TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE google_volume ADD user_id TEXT NOT NULL');
        $this->addSql('ALTER TABLE google_volume ADD page_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE saved_book ADD book_list VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE saved_book ADD book_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE saved_book DROP page_count');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_event DROP CONSTRAINT FK_356C1FDE9CA37C16');
        $this->addSql('DROP TABLE book_event');
        $this->addSql('ALTER TABLE google_volume DROP title');
        $this->addSql('ALTER TABLE google_volume DROP authors');
        $this->addSql('ALTER TABLE google_volume DROP categories');
        $this->addSql('ALTER TABLE google_volume DROP description');
        $this->addSql('ALTER TABLE google_volume DROP thumbnail');
        $this->addSql('ALTER TABLE google_volume DROP image');
        $this->addSql('ALTER TABLE google_volume DROP published_date');
        $this->addSql('ALTER TABLE google_volume DROP publisher');
        $this->addSql('ALTER TABLE google_volume DROP subtitle');
        $this->addSql('ALTER TABLE google_volume DROP user_id');
        $this->addSql('ALTER TABLE google_volume DROP page_count');
        $this->addSql('ALTER TABLE saved_book ADD page_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE saved_book DROP book_list');
        $this->addSql('ALTER TABLE saved_book DROP book_status');
    }
}
