<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806083052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fonction_valide_historique (id INT AUTO_INCREMENT NOT NULL, fonction_valide_id INT NOT NULL, date_modification DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modifie_par VARCHAR(255) DEFAULT NULL, INDEX IDX_B30980361ECA5C78 (fonction_valide_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fonction_valide_historique ADD CONSTRAINT FK_B30980361ECA5C78 FOREIGN KEY (fonction_valide_id) REFERENCES fonction_valide (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide_historique DROP FOREIGN KEY FK_B30980361ECA5C78');
        $this->addSql('DROP TABLE fonction_valide_historique');
    }
}
