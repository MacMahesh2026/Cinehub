<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914070029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screen ADD theatre_id INT NOT NULL');
        $this->addSql('ALTER TABLE screen ADD CONSTRAINT FK_DF4C6130C80060CD FOREIGN KEY (theatre_id) REFERENCES theatre (id)');
        $this->addSql('CREATE INDEX IDX_DF4C6130C80060CD ON screen (theatre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screen DROP FOREIGN KEY FK_DF4C6130C80060CD');
        $this->addSql('DROP INDEX IDX_DF4C6130C80060CD ON screen');
        $this->addSql('ALTER TABLE screen DROP theatre_id');
    }
}
