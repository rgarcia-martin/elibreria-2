<?php
// src/Domain/Invoicing/Invoice.php
declare(strict_types=1);

namespace App\Domain\Invoicing;

use App\Domain\Common\Money;
use App\Domain\Sales\SaleId;
use App\Domain\Identity\CompanyIdentityId;

class Invoice
{
    /** @var InvoiceLine[] */
    private array $lines = [];

    public function __construct(
        private InvoiceId $id,
        private string $number,
        private \DateTimeImmutable $issuedAt,
        private InvoiceType $type,
        private InvoiceFormat $format,
        private CompanyIdentityId $issuerIdentityId,
        private SaleId $saleId,
        private ?InvoiceId $rectifies = null
    ) {}

    public function id(): InvoiceId { return $this->id; }
    public function number(): string { return $this->number; }
    public function saleId(): SaleId { return $this->saleId; }
    public function format(): InvoiceFormat { return $this->format; }
    public function rectifies(): ?InvoiceId { return $this->rectifies; }

    public function addLine(InvoiceLine $l): void { $this->lines[] = $l; }
    /** @return InvoiceLine[] */ public function lines(): array { return $this->lines; }

    public function total(): Money { $sum = Money::zero(); foreach ($this->lines as $l) $sum = $sum->add($l->total()); return $sum; }
}
