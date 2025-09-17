<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/InvoiceMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Invoicing\{Invoice, InvoiceId, InvoiceFormat, InvoiceType, InvoiceLine};
use App\Domain\Common\{Money, Percentage};
use App\Domain\Sales\SaleId;
use App\Domain\Identity\CompanyIdentityId;
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineInvoice, DoctrineInvoiceLine};

final class InvoiceMapper
{
    public function toEntity(Invoice $i): DoctrineInvoice
    {
        $e = new DoctrineInvoice();
        $e->id = (string)$i->id();
        $e->number = $i->number();
        $e->issued_at = new \DateTimeImmutable();
        $e->type = $i->rectifies() ? InvoiceType::RECTIFICATION->value : $i->format()->value;
        $e->format = $i->format()->value;
        $e->issuer_identity_id = (string)$i->id(); // corregido por servicio al persistir
        $e->sale_id = (string)$i->saleId();
        $e->rectifies_id = $i->rectifies()?->value;
        return $e;
    }

    public function lineToEntity(InvoiceLine $l, string $invoiceId): DoctrineInvoiceLine
    {
        $e = new DoctrineInvoiceLine();
        $e->invoice_id = $invoiceId;
        $e->description = (new \ReflectionClass($l))->getProperty('description')->getValue($l);
        $e->units = (new \ReflectionClass($l))->getProperty('units')->getValue($l);
        $unitPrice = (new \ReflectionClass($l))->getProperty('unitPrice')->getValue($l);
        $e->unit_price_amount = $unitPrice->amount;
        $e->unit_price_currency = $unitPrice->currency;
        $tax = (new \ReflectionClass($l))->getProperty('taxRate')->getValue($l);
        $e->tax_rate = $tax->value;
        return $e;
    }

    public function toDomain(DoctrineInvoice $e, array $lines): Invoice
    {
        $inv = new Invoice(
            new InvoiceId($e->id),
            $e->number,
            $e->issued_at,
            InvoiceType::from($e->type),
            InvoiceFormat::from($e->format),
            new CompanyIdentityId($e->issuer_identity_id),
            new SaleId($e->sale_id),
            $e->rectifies_id ? new InvoiceId($e->rectifies_id) : null
        );
        foreach ($lines as $el) {
            $inv->addLine(new InvoiceLine(
                $el->description,
                $el->units,
                new Money($el->unit_price_amount, $el->unit_price_currency),
                new Percentage($el->tax_rate)
            ));
        }
        return $inv;
    }
}
