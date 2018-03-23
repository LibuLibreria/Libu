<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323121954 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE venta DROP FOREIGN KEY venta_ibfk_3');
        $this->addSql('DROP INDEX cliente ON venta');
        $this->addSql('ALTER TABLE venta CHANGE cliente tipocliente INT DEFAULT NULL');
        $this->addSql('ALTER TABLE venta ADD CONSTRAINT FK_8FE7EE5598968ABE FOREIGN KEY (tipocliente) REFERENCES tipocliente (id_cli)');
        $this->addSql('CREATE INDEX tipocliente ON venta (tipocliente)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE venta DROP FOREIGN KEY FK_8FE7EE5598968ABE');
        $this->addSql('DROP INDEX tipocliente ON venta');
        $this->addSql('ALTER TABLE venta CHANGE tipocliente cliente INT DEFAULT NULL');
        $this->addSql('ALTER TABLE venta ADD CONSTRAINT venta_ibfk_3 FOREIGN KEY (cliente) REFERENCES cliente (id_cli)');
        $this->addSql('CREATE INDEX cliente ON venta (cliente)');
    }
}
