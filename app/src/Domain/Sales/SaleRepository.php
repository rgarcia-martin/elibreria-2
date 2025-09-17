<?php
// src/Domain/Sales/SaleRepository.php
declare(strict_types=1);

namespace App\Domain\Sales;

interface SaleRepository
{
    public function byId(SaleId $id): ?Sale;
    public function save(Sale $s): void;
}
