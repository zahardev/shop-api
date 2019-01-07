<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190107011139 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE receipt (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vat_class (id INT AUTO_INCREMENT NOT NULL, percent SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE receipt_item (id INT AUTO_INCREMENT NOT NULL, receipt_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_89601E922B5CA896 (receipt_id), INDEX IDX_89601E924584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, vat_class_id INT NOT NULL, barcode BIGINT NOT NULL, cost DOUBLE PRECISION NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D34A04ADFC93D4BF (vat_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE receipt_item ADD CONSTRAINT FK_89601E922B5CA896 FOREIGN KEY (receipt_id) REFERENCES receipt (id)');
        $this->addSql('ALTER TABLE receipt_item ADD CONSTRAINT FK_89601E924584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADFC93D4BF FOREIGN KEY (vat_class_id) REFERENCES vat_class (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE receipt_item DROP FOREIGN KEY FK_89601E922B5CA896');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADFC93D4BF');
        $this->addSql('ALTER TABLE receipt_item DROP FOREIGN KEY FK_89601E924584665A');
        $this->addSql('DROP TABLE receipt');
        $this->addSql('DROP TABLE vat_class');
        $this->addSql('DROP TABLE receipt_item');
        $this->addSql('DROP TABLE product');
    }
}
