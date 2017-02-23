<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170223135253 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tematica ADD activo VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE concepto ADD activo VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE tipo ADD activo VARCHAR(2) NOT NULL');
        $this->addSql('UPDATE tematica SET activo = "si"');
        $this->addSql('UPDATE concepto SET activo = "si"');
        $this->addSql('UPDATE tipo SET activo = "si"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE concepto DROP activo');
        $this->addSql('ALTER TABLE tematica DROP activo');
        $this->addSql('ALTER TABLE tipo DROP activo');
    }
}
