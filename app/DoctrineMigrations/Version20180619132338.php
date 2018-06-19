<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180619132338 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE analisis (id INT AUTO_INCREMENT NOT NULL, id_libro INT DEFAULT NULL, codigo INT DEFAULT NULL, titulo VARCHAR(100) DEFAULT NULL, isbn VARCHAR(20) DEFAULT NULL, autor VARCHAR(60) DEFAULT NULL, editorial VARCHAR(40) DEFAULT NULL, anno VARCHAR(6) DEFAULT NULL, precio DOUBLE PRECISION DEFAULT NULL, otros VARCHAR(50) DEFAULT NULL, fechaanalisis DATETIME DEFAULT NULL, plataforma VARCHAR(3) DEFAULT NULL, INDEX id_libro (id_libro), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analisis ADD CONSTRAINT FK_502A90AFB91CEC1B FOREIGN KEY (id_libro) REFERENCES libro (id_libro)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE analisis');
    }
}
