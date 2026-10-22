<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914072616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat ADD screen_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C366641A67722 FOREIGN KEY (screen_id) REFERENCES screen (id)');
        $this->addSql('CREATE INDEX IDX_3D5C366641A67722 ON seat (screen_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat DROP FOREIGN KEY FK_3D5C366641A67722');
        $this->addSql('DROP INDEX IDX_3D5C366641A67722 ON seat');
        $this->addSql('ALTER TABLE seat DROP screen_id');
    }
}
