<?php
// src/Infrastructure/Adapters/Invoicing/NullDigitalInvoiceEmitter.php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Invoicing;

use App\Domain\Ports\DigitalInvoiceEmitterPort;
use App\Domain\Invoicing\Invoice;

final class NullDigitalInvoiceEmitter implements DigitalInvoiceEmitterPort
{
    public function emit(Invoice $invoice): string
    {
        // No-op (útil para dev)
        return 'DIGITAL-EMIT-DISABLED';
    }
}
