<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/CompanyIdentityMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Identity\{CompanyIdentity, CompanyIdentityId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineCompanyIdentity;

final class CompanyIdentityMapper
{
    public function toEntity(CompanyIdentity $c): DoctrineCompanyIdentity
    {
        $e = new DoctrineCompanyIdentity();
        $e->id = (string)$c->id();
        $e->legal_name = $c->legalName();
        $e->tax_id = $c->taxId();
        $e->address = $c->address();
        $e->e_invoicing_id = $c->eInvoicingId();
        return $e;
    }
    public function toDomain(DoctrineCompanyIdentity $e): CompanyIdentity
    {
        return new CompanyIdentity(
            new CompanyIdentityId($e->id),
            $e->legal_name,
            $e->tax_id,
            $e->address,
            $e->e_invoicing_id
        );
    }
}
