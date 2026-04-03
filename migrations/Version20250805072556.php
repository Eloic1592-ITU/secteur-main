<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805072556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide ADD deleted_at DATE DEFAULT NULL, DROP is_deleted');
        $this->addSql('ALTER TABLE origine_valide CHANGE deleted_at deleted_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE secteur_valide ADD deleted_at DATE DEFAULT NULL, DROP is_deleted');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide ADD is_deleted TINYINT(1) NOT NULL, DROP deleted_at');
        $this->addSql('ALTER TABLE origine_valide CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE secteur_valide ADD is_deleted TINYINT(1) NOT NULL, DROP deleted_at');
    }
}
