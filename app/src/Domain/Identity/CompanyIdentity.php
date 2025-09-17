<?php
// src/Domain/Identity/CompanyIdentity.php
declare(strict_types=1);

namespace App\Domain\Identity;

final class CompanyIdentity
{
    public function __construct(
        private CompanyIdentityId $id,
        private string $legalName,
        private string $taxId,
        private string $address,
        private ?string $eInvoicingId = null
    ) {}
    public function id(): CompanyIdentityId { return $this->id; }
    public function legalName(): string { return $this->legalName; }
    public function taxId(): string { return $this->taxId; }
    public function address(): string { return $this->address; }
    public function eInvoicingId(): ?string { return $this->eInvoicingId; }
}
