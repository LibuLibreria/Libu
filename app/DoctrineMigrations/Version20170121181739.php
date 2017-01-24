<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170121181739 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE producto ADD activo VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE venta ADD CONSTRAINT FK_8FE7EE55648388D0 FOREIGN KEY (concepto) REFERENCES concepto (id)');
        $this->addSql('CREATE INDEX concepto ON venta (concepto)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE producto DROP activo');
        $this->addSql('ALTER TABLE venta DROP FOREIGN KEY FK_8FE7EE55648388D0');
        $this->addSql('DROP INDEX concepto ON venta');
    }
}
