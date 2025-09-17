<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/ProviderMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Partners\{Provider, ProviderId, ProviderContact};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineProvider;

final class ProviderMapper
{
    public function toEntity(Provider $p): DoctrineProvider
    {
        $e = new DoctrineProvider();
        $e->id = (string)$p->id();
        $e->name = $p->name();
        $e->tax_id = $p->taxId();
        $e->email = $p->contact()->email();
        $e->phone = $p->contact()->phone();
        $e->address = $p->contact()->address();
        $e->default_consignment_days = $p->defaultConsignmentDays();
        return $e;
    }

    public function toDomain(DoctrineProvider $e): Provider
    {
        return new Provider(
            new ProviderId($e->id),
            $e->name,
            $e->tax_id,
            new ProviderContact($e->email, $e->phone, $e->address),
            $e->default_consignment_days
        );
    }
}
