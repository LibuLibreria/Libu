<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170519104939 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tapas ADD codigo VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE conservacion ADD codigo VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE tipolibro ADD codigo VARCHAR(4) DEFAULT NULL');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("1", "Nuevo")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("2", "Como nuevo")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("3", "Excelente")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("4", "Muy bien")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("5", "Bien")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("6", "Aceptable")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("7", "Regular")');
        $this->addSql('INSERT INTO conservacion (codigo, conservacion) VALUES ("8", "Mal estado")');
        $this->addSql('INSERT INTO tapas (codigo, tapa) VALUES ("1", "Tapa blanda")');
        $this->addSql('INSERT INTO tapas (codigo, tapa) VALUES ("2", "Tapa dura")');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM conservacion');
        $this->addSql('DELETE FROM tapas');
        $this->addSql('ALTER TABLE conservacion DROP codigo');
        $this->addSql('ALTER TABLE tapas DROP codigo');
        $this->addSql('ALTER TABLE tipolibro DROP codigo');
    }
}
