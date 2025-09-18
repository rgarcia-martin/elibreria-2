<?php
// src/Domain/Partners/Provider.php
declare(strict_types=1);

namespace App\Domain\Partners;

class Provider
{
    public function __construct(
        private ProviderId $id,
        private string $name,
        private ?string $taxId,
        private ProviderContact $contact,
        private ?int $defaultConsignmentDays = null
    ) {}

    public function id(): ProviderId { return $this->id; }
    public function name(): string { return $this->name; }
    public function taxId(): ?string { return $this->taxId; }
    public function contact(): ProviderContact { return $this->contact; }
    public function defaultConsignmentDays(): ?int { return $this->defaultConsignmentDays; }
}
