<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Get rid of vat_class table
 */
final class Version20190108210159 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADFC93D4BF');
        $this->addSql('DROP TABLE vat_class');
        $this->addSql('ALTER TABLE receipt_item CHANGE receipt_id receipt_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_D34A04ADFC93D4BF ON product');
        $this->addSql('ALTER TABLE product CHANGE vat_class_id vat_class INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vat_class (id INT AUTO_INCREMENT NOT NULL, percent SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE product CHANGE vat_class vat_class_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADFC93D4BF FOREIGN KEY (vat_class_id) REFERENCES vat_class (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADFC93D4BF ON product (vat_class_id)');
        $this->addSql('ALTER TABLE receipt_item CHANGE receipt_id receipt_id INT NOT NULL');
    }
}
