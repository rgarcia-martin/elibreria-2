<?php
// src/Domain/Ports/InvoiceNumberSequencerPort.php
declare(strict_types=1);

namespace App\Domain\Ports;

interface InvoiceNumberSequencerPort
{
    public function nextNumberFor(string $seriesKey): string;
}
