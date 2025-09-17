<?php
// migrations/Version20250917_000001.php
declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Primera migración: tablas base para catálogo, partners, ubicaciones, inventario, ventas e invoicing.
 */
final class Version20250917_000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tablas iniciales: articles, providers, locations, goods_receipts(+lines+photos), stock_units, sales(+lines), invoices(+lines), company_identities, users';
    }

    public function up(Schema $schema): void
    {
        // articles
        $this->addSql('CREATE TABLE articles (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            barcode VARCHAR(64) DEFAULT NULL,
            base_price_amount INT NOT NULL,
            base_price_currency VARCHAR(3) NOT NULL,
            tax_rate DOUBLE PRECISION NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE INDEX IDX_ARTICLES_BARCODE ON articles (barcode)');

        // providers
        $this->addSql('CREATE TABLE providers (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            tax_id VARCHAR(64) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(64) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            default_consignment_days INT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // locations
        $this->addSql('CREATE TABLE locations (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            parent_id VARCHAR(36) DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_LOC_PARENT (parent_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // goods_receipts
        $this->addSql('CREATE TABLE goods_receipts (
            id VARCHAR(36) NOT NULL,
            type VARCHAR(16) NOT NULL,
            received_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            provider_id VARCHAR(36) DEFAULT NULL,
            return_due_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            state VARCHAR(16) NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_GR_PROVIDER (provider_id),
            INDEX IDX_GR_RETURN_DUE (return_due_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // goods_receipt_lines
        $this->addSql('CREATE TABLE goods_receipt_lines (
            id VARCHAR(36) NOT NULL,
            receipt_id VARCHAR(36) NOT NULL,
            article_id VARCHAR(36) NOT NULL,
            quantity INT NOT NULL,
            unit_cost_amount INT NOT NULL,
            unit_cost_currency VARCHAR(3) NOT NULL,
            merchant_share DOUBLE PRECISION NOT NULL,
            provider_share DOUBLE PRECISION NOT NULL,
            initial_location_id VARCHAR(36) DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_GRL_RECEIPT (receipt_id),
            INDEX IDX_GRL_ARTICLE (article_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // goods_receipt_photos
        $this->addSql('CREATE TABLE goods_receipt_photos (
            id VARCHAR(36) NOT NULL,
            receipt_id VARCHAR(36) NOT NULL,
            uri VARCHAR(1024) NOT NULL,
            mime VARCHAR(64) NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_GRP_RECEIPT (receipt_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // stock_units
        $this->addSql('CREATE TABLE stock_units (
            id VARCHAR(36) NOT NULL,
            article_id VARCHAR(36) NOT NULL,
            origin_receipt_id VARCHAR(36) NOT NULL,
            provider_id VARCHAR(36) DEFAULT NULL,
            merchant_share DOUBLE PRECISION NOT NULL,
            provider_share DOUBLE PRECISION NOT NULL,
            unit_cost_amount INT NOT NULL,
            unit_cost_currency VARCHAR(3) NOT NULL,
            location_id VARCHAR(36) NOT NULL,
            status VARCHAR(16) NOT NULL,
            sold_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            INDEX IDX_SU_ARTICLE (article_id),
            INDEX IDX_SU_ORIGIN (origin_receipt_id),
            INDEX IDX_SU_PROVIDER (provider_id),
            INDEX IDX_SU_LOCATION (location_id),
            INDEX IDX_SU_STATUS (status)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // sales
        $this->addSql('CREATE TABLE sales (
            id VARCHAR(36) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            status VARCHAR(16) NOT NULL,
            global_discount_fixed_amount INT DEFAULT NULL,
            global_discount_percent DOUBLE PRECISION DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_SALES_STATUS (status)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // sale_lines
        $this->addSql('CREATE TABLE sale_lines (
            id VARCHAR(36) NOT NULL,
            sale_id VARCHAR(36) NOT NULL,
            article_id VARCHAR(36) NOT NULL,
            quantity INT NOT NULL,
            unit_price_amount INT NOT NULL,
            unit_price_currency VARCHAR(3) NOT NULL,
            discount_fixed_amount INT DEFAULT NULL,
            discount_percent DOUBLE PRECISION DEFAULT NULL,
            tax_rate DOUBLE PRECISION NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_SL_SALE (sale_id),
            INDEX IDX_SL_ARTICLE (article_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // invoices
        $this->addSql('CREATE TABLE invoices (
            id VARCHAR(36) NOT NULL,
            number VARCHAR(64) NOT NULL,
            issued_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            type VARCHAR(16) NOT NULL,
            format VARCHAR(16) NOT NULL,
            issuer_identity_id VARCHAR(36) NOT NULL,
            sale_id VARCHAR(36) NOT NULL,
            rectifies_id VARCHAR(36) DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_INVOICE_NUMBER (number),
            INDEX IDX_INV_SALE (sale_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // invoice_lines
        $this->addSql('CREATE TABLE invoice_lines (
            id INT AUTO_INCREMENT NOT NULL,
            invoice_id VARCHAR(36) NOT NULL,
            description VARCHAR(255) NOT NULL,
            units INT NOT NULL,
            unit_price_amount INT NOT NULL,
            unit_price_currency VARCHAR(3) NOT NULL,
            tax_rate DOUBLE PRECISION NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_IL_INV (invoice_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // company_identities
        $this->addSql('CREATE TABLE company_identities (
            id VARCHAR(36) NOT NULL,
            legal_name VARCHAR(255) NOT NULL,
            tax_id VARCHAR(64) NOT NULL,
            address VARCHAR(255) NOT NULL,
            e_invoicing_id VARCHAR(128) DEFAULT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_IDENTITY_TAXID (tax_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // users
        $this->addSql('CREATE TABLE users (
            id VARCHAR(36) NOT NULL,
            email VARCHAR(180) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_USER_EMAIL (email)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // FKs pragmáticas (opcionales, puedes ampliarlas):
        $this->addSql('ALTER TABLE goods_receipt_lines ADD CONSTRAINT FK_GRL_RECEIPT FOREIGN KEY (receipt_id) REFERENCES goods_receipts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE goods_receipt_photos ADD CONSTRAINT FK_GRP_RECEIPT FOREIGN KEY (receipt_id) REFERENCES goods_receipts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sale_lines ADD CONSTRAINT FK_SL_SALE FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_lines ADD CONSTRAINT FK_IL_INV FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice_lines DROP FOREIGN KEY FK_IL_INV');
        $this->addSql('ALTER TABLE sale_lines DROP FOREIGN KEY FK_SL_SALE');
        $this->addSql('ALTER TABLE goods_receipt_photos DROP FOREIGN KEY FK_GRP_RECEIPT');
        $this->addSql('ALTER TABLE goods_receipt_lines DROP FOREIGN KEY FK_GRL_RECEIPT');

        $this->addSql('DROP TABLE invoice_lines');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE sale_lines');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE stock_units');
        $this->addSql('DROP TABLE goods_receipt_photos');
        $this->addSql('DROP TABLE goods_receipt_lines');
        $this->addSql('DROP TABLE goods_receipts');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE providers');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE company_identities');
        $this->addSql('DROP TABLE users');
    }
}
