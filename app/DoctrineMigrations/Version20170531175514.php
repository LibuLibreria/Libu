<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170531175514 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE libro ADD refabebooks VARCHAR(15) DEFAULT NULL, CHANGE titulo titulo VARCHAR(100) DEFAULT NULL, CHANGE autor autor VARCHAR(60) DEFAULT NULL, CHANGE notas notas VARCHAR(200) DEFAULT NULL, CHANGE estanteria estanteria VARCHAR(4) DEFAULT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE libro DROP refabebooks, CHANGE titulo titulo VARCHAR(40) DEFAULT NULL COLLATE latin1_swedish_ci, CHANGE autor autor VARCHAR(40) DEFAULT NULL COLLATE latin1_swedish_ci, CHANGE notas notas VARCHAR(40) DEFAULT NULL COLLATE latin1_swedish_ci, CHANGE estanteria estanteria INT DEFAULT NULL');

    }
}
