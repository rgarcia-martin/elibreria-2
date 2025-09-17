<?php
// src/Domain/Identity/CompanyIdentityRepository.php
declare(strict_types=1);

namespace App\Domain\Identity;

interface CompanyIdentityRepository
{
    public function byId(CompanyIdentityId $id): ?CompanyIdentity;
    public function save(CompanyIdentity $c): void;
}
