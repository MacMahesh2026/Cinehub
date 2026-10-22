<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914112849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE showtime (id INT AUTO_INCREMENT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, price NUMERIC(10, 2) NOT NULL, movie_id INT NOT NULL, screen_id INT NOT NULL, INDEX IDX_3248D918F93B6FC (movie_id), INDEX IDX_3248D9141A67722 (screen_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D918F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE showtime ADD CONSTRAINT FK_3248D9141A67722 FOREIGN KEY (screen_id) REFERENCES screen (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D918F93B6FC');
        $this->addSql('ALTER TABLE showtime DROP FOREIGN KEY FK_3248D9141A67722');
        $this->addSql('DROP TABLE showtime');
    }
}
