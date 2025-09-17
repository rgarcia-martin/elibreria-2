<?php
// src/Infrastructure/Adapters/Invoicing/InMemoryInvoiceNumberSequencer.php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Invoicing;

use App\Domain\Ports\InvoiceNumberSequencerPort;

final class InMemoryInvoiceNumberSequencer implements InvoiceNumberSequencerPort
{
    /** @var array<string,int> */
    private array $counters = [];

    public function nextNumberFor(string $seriesKey): string
    {
        $n = ($this->counters[$seriesKey] ?? 0) + 1;
        $this->counters[$seriesKey] = $n;
        return sprintf('%s-%06d', strtoupper($seriesKey), $n);
    }
}
