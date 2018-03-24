<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323234256 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE clientefactura (id INT AUTO_INCREMENT NOT NULL, cliente INT DEFAULT NULL, venta INT DEFAULT NULL, numfactura VARCHAR(30) DEFAULT NULL, diaHora DATETIME DEFAULT NULL, INDEX cliente (cliente), INDEX venta (venta), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DELETE FROM cliente');

        $this->addSql('ALTER TABLE cliente MODIFY id_cli INT NOT NULL');
        $this->addSql('ALTER TABLE cliente DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE cliente CHANGE id_cli id INT'); 
        $this->addSql('ALTER TABLE cliente ADD PRIMARY KEY (id)');              
        $this->addSql('ALTER TABLE cliente ADD nif_cif VARCHAR(40) DEFAULT NULL, ADD direccion VARCHAR(255) DEFAULT NULL, ADD otros VARCHAR(255) DEFAULT NULL, CHANGE nombre nombre VARCHAR(255) DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');

        $this->addSql('ALTER TABLE clientefactura ADD CONSTRAINT FK_BFDBABC1F41C9B25 FOREIGN KEY (cliente) REFERENCES cliente (id)');
        $this->addSql('ALTER TABLE clientefactura ADD CONSTRAINT FK_BFDBABC18FE7EE55 FOREIGN KEY (venta) REFERENCES venta (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE clientefactura');
        $this->addSql('ALTER TABLE cliente MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE cliente DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE cliente CHANGE id id_cli INT');
        $this->addSql('ALTER TABLE cliente ADD PRIMARY KEY (id_cli)');        
        $this->addSql('ALTER TABLE cliente DROP nif_cif, DROP direccion, DROP otros, CHANGE nombre nombre VARCHAR(25) DEFAULT NULL COLLATE latin1_swedish_ci, CHANGE id_cli id_cli INT AUTO_INCREMENT NOT NULL');

    }
}
