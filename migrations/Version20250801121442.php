<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801121442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE secteur_valide (id INT AUTO_INCREMENT NOT NULL, annee VARCHAR(10) NOT NULL, nummois VARCHAR(10) NOT NULL, ensemble VARCHAR(20) NOT NULL, prodvivr_n_t VARCHAR(20) NOT NULL, prodvivr_t_n_riz VARCHAR(20) NOT NULL, prodvivr_t_riz VARCHAR(20) NOT NULL, prodmanufind VARCHAR(20) NOT NULL, prodmanufart VARCHAR(20) NOT NULL, servpubl VARCHAR(20) NOT NULL, servpriv VARCHAR(20) NOT NULL, ppn VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE secteur_valide');
    }
}
