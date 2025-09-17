<?php
// src/Domain/Ports/DigitalInvoiceEmitterPort.php
declare(strict_types=1);

namespace App\Domain\Ports;

use App\Domain\Invoicing\Invoice;

interface DigitalInvoiceEmitterPort
{
    /** Devuelve id/URL del registro emitido (Facturae/Peppol/etc.) */
    public function emit(Invoice $invoice): string;
}
