<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260916090556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking_seat (id INT AUTO_INCREMENT NOT NULL, booking_id INT NOT NULL, seat_id INT NOT NULL, INDEX IDX_25CD14283301C60 (booking_id), INDEX IDX_25CD1428C1DAFE35 (seat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booking_seat ADD CONSTRAINT FK_25CD14283301C60 FOREIGN KEY (booking_id) REFERENCES booking (id)');
        $this->addSql('ALTER TABLE booking_seat ADD CONSTRAINT FK_25CD1428C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking_seat DROP FOREIGN KEY FK_25CD14283301C60');
        $this->addSql('ALTER TABLE booking_seat DROP FOREIGN KEY FK_25CD1428C1DAFE35');
        $this->addSql('DROP TABLE booking_seat');
    }
}
