<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160929172037 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE venta ADD ingreso_libros FLOAT DEFAULT 0');
        $this->addSql('UPDATE venta SET libros_3=0 WHERE libros_3 IS NULL');
        $this->addSql('UPDATE venta SET libros_1=0 WHERE libros_1 IS NULL');
        $this->addSql('UPDATE venta 
            SET ingreso_libros =  
            CASE 
                WHEN libros_3=0 THEN libros_1 
                WHEN libros_3=1 THEN libros_1 + 3 
                WHEN libros_3=2 THEN libros_1 + 5 
                WHEN libros_3=3 THEN libros_1 + 8 
                WHEN libros_3=4 THEN libros_1 + 10 
                WHEN libros_3 > 4 THEN libros_1 + (libros_3*2)  
                ELSE 9999 
            END');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE venta DROP ingreso_libros');
    }
}
