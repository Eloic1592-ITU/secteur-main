<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801104305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide CHANGE annee annee VARCHAR(255) NOT NULL, CHANGE nummois nummois VARCHAR(255) NOT NULL, CHANGE ensemble ensemble VARCHAR(255) NOT NULL, CHANGE prod_alim_bois_nalc prod_alim_bois_nalc VARCHAR(255) NOT NULL, CHANGE tissu_vetement tissu_vetement VARCHAR(255) NOT NULL, CHANGE logt_et_combust logt_et_combust VARCHAR(255) NOT NULL, CHANGE am_eqmena_entc_m am_eqmena_entc_m VARCHAR(255) NOT NULL, CHANGE sante sante VARCHAR(255) NOT NULL, CHANGE transports transports VARCHAR(255) NOT NULL, CHANGE loisir_spect_cult loisir_spect_cult VARCHAR(255) NOT NULL, CHANGE enseignement enseignement VARCHAR(255) NOT NULL, CHANGE hotel_cafe_rest hotel_cafe_rest VARCHAR(255) NOT NULL, CHANGE autres_bien_serv autres_bien_serv VARCHAR(255) NOT NULL, CHANGE bois_alc_tab bois_alc_tab VARCHAR(255) NOT NULL, CHANGE communications communications VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonction_valide CHANGE annee annee INT NOT NULL, CHANGE nummois nummois INT NOT NULL, CHANGE ensemble ensemble INT NOT NULL, CHANGE prod_alim_bois_nalc prod_alim_bois_nalc INT NOT NULL, CHANGE tissu_vetement tissu_vetement INT NOT NULL, CHANGE logt_et_combust logt_et_combust INT NOT NULL, CHANGE am_eqmena_entc_m am_eqmena_entc_m INT NOT NULL, CHANGE sante sante INT NOT NULL, CHANGE transports transports INT NOT NULL, CHANGE loisir_spect_cult loisir_spect_cult INT NOT NULL, CHANGE enseignement enseignement INT NOT NULL, CHANGE hotel_cafe_rest hotel_cafe_rest INT NOT NULL, CHANGE autres_bien_serv autres_bien_serv INT NOT NULL, CHANGE bois_alc_tab bois_alc_tab INT NOT NULL, CHANGE communications communications INT NOT NULL');
    }
}
