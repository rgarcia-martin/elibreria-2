<?php
// src/Domain/Invoicing/InvoiceRepository.php
declare(strict_types=1);

namespace App\Domain\Invoicing;

interface InvoiceRepository
{
    public function byId(InvoiceId $id): ?Invoice;
    public function save(Invoice $i): void;
}
