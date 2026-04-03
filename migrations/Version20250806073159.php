<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806073159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE origine_valide_historique (id INT AUTO_INCREMENT NOT NULL, origine_valide_id INT NOT NULL, date_modification DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modifie_par VARCHAR(255) DEFAULT NULL, INDEX IDX_F7AB30D0DDD45481 (origine_valide_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE origine_valide_historique ADD CONSTRAINT FK_F7AB30D0DDD45481 FOREIGN KEY (origine_valide_id) REFERENCES origine_valide (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE origine_valide_historique DROP FOREIGN KEY FK_F7AB30D0DDD45481');
        $this->addSql('DROP TABLE origine_valide_historique');
    }
}
