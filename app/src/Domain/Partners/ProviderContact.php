<?php
// src/Domain/Partners/ProviderContact.php
declare(strict_types=1);

namespace App\Domain\Partners;

final class ProviderContact
{
    public function __construct(
        private string $email,
        private ?string $phone = null,
        private ?string $address = null
    ) {}
    public function email(): string { return $this->email; }
    public function phone(): ?string { return $this->phone; }
    public function address(): ?string { return $this->address; }
}
