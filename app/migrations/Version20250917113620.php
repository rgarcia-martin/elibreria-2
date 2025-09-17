<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917113620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE articles (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, barcode VARCHAR(64) DEFAULT NULL, base_price_amount INT NOT NULL, base_price_currency VARCHAR(3) NOT NULL, tax_rate DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE company_identities (id VARCHAR(36) NOT NULL, legal_name VARCHAR(255) NOT NULL, tax_id VARCHAR(64) NOT NULL, address VARCHAR(255) NOT NULL, e_invoicing_id VARCHAR(128) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE goods_receipt_lines (id VARCHAR(36) NOT NULL, article_id VARCHAR(36) NOT NULL, quantity INT NOT NULL, unit_cost_amount INT NOT NULL, unit_cost_currency VARCHAR(3) NOT NULL, merchant_share DOUBLE PRECISION NOT NULL, provider_share DOUBLE PRECISION NOT NULL, initial_location_id VARCHAR(36) DEFAULT NULL, receipt_id VARCHAR(36) NOT NULL, INDEX IDX_704AA0612B5CA896 (receipt_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE goods_receipt_photos (id VARCHAR(36) NOT NULL, uri VARCHAR(1024) NOT NULL, mime VARCHAR(64) NOT NULL, receipt_id VARCHAR(36) NOT NULL, INDEX IDX_2B90CC4A2B5CA896 (receipt_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE goods_receipts (id VARCHAR(36) NOT NULL, type VARCHAR(16) NOT NULL, received_at DATETIME NOT NULL, provider_id VARCHAR(36) DEFAULT NULL, return_due_at DATETIME DEFAULT NULL, state VARCHAR(16) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invoice_lines (id INT AUTO_INCREMENT NOT NULL, invoice_id VARCHAR(36) NOT NULL, description VARCHAR(255) NOT NULL, units INT NOT NULL, unit_price_amount INT NOT NULL, unit_price_currency VARCHAR(3) NOT NULL, tax_rate DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invoices (id VARCHAR(36) NOT NULL, number VARCHAR(64) NOT NULL, issued_at DATETIME NOT NULL, type VARCHAR(16) NOT NULL, format VARCHAR(16) NOT NULL, issuer_identity_id VARCHAR(36) NOT NULL, sale_id VARCHAR(36) NOT NULL, rectifies_id VARCHAR(36) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE locations (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, parent_id VARCHAR(36) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE providers (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, tax_id VARCHAR(64) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(64) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, default_consignment_days INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sale_lines (id VARCHAR(36) NOT NULL, sale_id VARCHAR(36) NOT NULL, article_id VARCHAR(36) NOT NULL, quantity INT NOT NULL, unit_price_amount INT NOT NULL, unit_price_currency VARCHAR(3) NOT NULL, discount_fixed_amount INT DEFAULT NULL, discount_percent DOUBLE PRECISION DEFAULT NULL, tax_rate DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sales (id VARCHAR(36) NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(16) NOT NULL, global_discount_fixed_amount INT DEFAULT NULL, global_discount_percent DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_units (id VARCHAR(36) NOT NULL, article_id VARCHAR(36) NOT NULL, origin_receipt_id VARCHAR(36) NOT NULL, provider_id VARCHAR(36) DEFAULT NULL, merchant_share DOUBLE PRECISION NOT NULL, provider_share DOUBLE PRECISION NOT NULL, unit_cost_amount INT NOT NULL, unit_cost_currency VARCHAR(3) NOT NULL, location_id VARCHAR(36) NOT NULL, status VARCHAR(16) NOT NULL, sold_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE goods_receipt_lines ADD CONSTRAINT FK_704AA0612B5CA896 FOREIGN KEY (receipt_id) REFERENCES goods_receipts (id)');
        $this->addSql('ALTER TABLE goods_receipt_photos ADD CONSTRAINT FK_2B90CC4A2B5CA896 FOREIGN KEY (receipt_id) REFERENCES goods_receipts (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goods_receipt_lines DROP FOREIGN KEY FK_704AA0612B5CA896');
        $this->addSql('ALTER TABLE goods_receipt_photos DROP FOREIGN KEY FK_2B90CC4A2B5CA896');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE company_identities');
        $this->addSql('DROP TABLE goods_receipt_lines');
        $this->addSql('DROP TABLE goods_receipt_photos');
        $this->addSql('DROP TABLE goods_receipts');
        $this->addSql('DROP TABLE invoice_lines');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE providers');
        $this->addSql('DROP TABLE sale_lines');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE stock_units');
        $this->addSql('DROP TABLE users');
    }
}
