<?php
// src/Domain/Partners/ProviderRepository.php
declare(strict_types=1);

namespace App\Domain\Partners;

interface ProviderRepository
{
    public function byId(ProviderId $id): ?Provider;
    public function save(Provider $p): void;
}
