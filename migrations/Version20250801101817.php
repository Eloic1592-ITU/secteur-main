<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801101817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fonction_valide (id INT AUTO_INCREMENT NOT NULL, annee INT NOT NULL, nummois INT NOT NULL, ensemble INT NOT NULL, prod_alim_bois_nalc INT NOT NULL, tissu_vetement INT NOT NULL, logt_et_combust INT NOT NULL, am_eqmena_entc_m INT NOT NULL, sante INT NOT NULL, transports INT NOT NULL, loisir_spect_cult INT NOT NULL, enseignement INT NOT NULL, hotel_cafe_rest INT NOT NULL, autres_bien_serv INT NOT NULL, bois_alc_tab INT NOT NULL, communications INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fonction_valide');
    }
}
