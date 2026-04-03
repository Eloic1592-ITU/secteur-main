<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250809190502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide_historique ADD created_at DATETIME NOT NULL, ADD created_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE secteur_valide_historique ADD created_at DATETIME NOT NULL, ADD created_by VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide_historique DROP created_at, DROP created_by');
        $this->addSql('ALTER TABLE secteur_valide_historique DROP created_at, DROP created_by');
    }
}
