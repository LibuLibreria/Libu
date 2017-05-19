<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170518101914 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tapas (id INT AUTO_INCREMENT NOT NULL, tapa VARCHAR(30) NOT NULL, abreviatura VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conservacion (id INT AUTO_INCREMENT NOT NULL, conservacion VARCHAR(30) NOT NULL, abreviatura VARCHAR(30) DEFAULT NULL, otros VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipolibro (id INT AUTO_INCREMENT NOT NULL, tipolibro VARCHAR(40) NOT NULL, datos VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE libro CHANGE descripcion descripcion VARCHAR(500) DEFAULT NULL, CHANGE tipo tipolibro INT DEFAULT NULL');
        $this->addSql('ALTER TABLE libro ADD CONSTRAINT FK_5799AD2B930A75 FOREIGN KEY (tipolibro) REFERENCES tipolibro (id)');
        $this->addSql('ALTER TABLE libro ADD CONSTRAINT FK_5799AD2BA8F13344 FOREIGN KEY (tapas) REFERENCES tapas (id)');
        $this->addSql('ALTER TABLE libro ADD CONSTRAINT FK_5799AD2B5D1A9D33 FOREIGN KEY (conservacion) REFERENCES conservacion (id)');
        $this->addSql('CREATE INDEX tipolibro ON libro (tipolibro)');
        $this->addSql('CREATE INDEX tapas ON libro (tapas)');
        $this->addSql('CREATE INDEX conservacion ON libro (conservacion)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE libro DROP FOREIGN KEY FK_5799AD2BA8F13344');
        $this->addSql('ALTER TABLE libro DROP FOREIGN KEY FK_5799AD2B5D1A9D33');
        $this->addSql('ALTER TABLE libro DROP FOREIGN KEY FK_5799AD2B930A75');
        $this->addSql('DROP TABLE tapas');
        $this->addSql('DROP TABLE conservacion');
        $this->addSql('DROP TABLE tipolibro');
        $this->addSql('DROP INDEX tipolibro ON libro');
        $this->addSql('DROP INDEX tapas ON libro');
        $this->addSql('DROP INDEX conservacion ON libro');
        $this->addSql('ALTER TABLE libro CHANGE descripcion descripcion VARCHAR(100) DEFAULT NULL COLLATE latin1_swedish_ci, CHANGE tipolibro tipo INT DEFAULT NULL');
    }
}
