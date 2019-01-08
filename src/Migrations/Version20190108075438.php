<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Added receipt and receipt_item calculation fields
 */
final class Version20190108075438 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE receipt ADD total DOUBLE PRECISION DEFAULT NULL, ADD total_vat DOUBLE PRECISION DEFAULT NULL, ADD total_with_vat DOUBLE PRECISION DEFAULT NULL, ADD total21 DOUBLE PRECISION DEFAULT NULL, ADD total_vat21 DOUBLE PRECISION DEFAULT NULL, ADD total_with_vat21 DOUBLE PRECISION DEFAULT NULL, ADD total6 DOUBLE PRECISION DEFAULT NULL, ADD total_vat6 DOUBLE PRECISION DEFAULT NULL, ADD total_with_vat6 DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE receipt_item ADD cost DOUBLE PRECISION NOT NULL, ADD vat_class INT NOT NULL, ADD vat DOUBLE PRECISION NOT NULL, ADD cost_with_vat DOUBLE PRECISION NOT NULL, ADD total DOUBLE PRECISION NOT NULL, ADD total_vat DOUBLE PRECISION NOT NULL, ADD total_with_vat DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE receipt DROP total, DROP total_vat, DROP total_with_vat, DROP total21, DROP total_vat21, DROP total_with_vat21, DROP total6, DROP total_vat6, DROP total_with_vat6');
        $this->addSql('ALTER TABLE receipt_item DROP cost, DROP vat_class, DROP vat, DROP cost_with_vat, DROP total, DROP total_vat, DROP total_with_vat');
    }
}
