<?php
// src/Domain/Invoicing/InvoicingService.php
declare(strict_types=1);

namespace App\Domain\Invoicing;

use App\Domain\Sales\{SaleId, SaleRepository};
use App\Domain\Identity\CompanyIdentityId;
use App\Domain\Ports\{InvoiceNumberSequencerPort, DigitalInvoiceEmitterPort};
use App\Domain\Common\Clock;

class InvoicingService
{
    public function __construct(
        private InvoiceRepository $invoices,
        private SaleRepository $sales,
        private InvoiceNumberSequencerPort $sequencer,
        private ?DigitalInvoiceEmitterPort $digitalEmitter,
        private Clock $clock
    ) {}

    public function generateForSale(
        CompanyIdentityId $issuerIdentity,
        string $seriesKey,
        InvoiceFormat $format,
        InvoiceType $type,
        SaleId $saleId
    ): Invoice {
        $sale = $this->sales->byId($saleId) ?? throw new \RuntimeException('Venta no encontrada');

        $invoice = new Invoice(
            new InvoiceId((string)\Ramsey\Uuid\Uuid::uuid4()),
            $this->sequencer->nextNumberFor($seriesKey),
            $this->clock->now(),
            $type,
            $format,
            $issuerIdentity,
            $sale->id()
        );

        foreach ($sale->lines() as $line) {
            $unitPriceNet = $line->totalAfterDiscount()->mul(1.0/$line->quantity()->units);
            $invoice->addLine(new InvoiceLine(
                description: 'Artículo '.$line->articleId()->value,
                units: $line->quantity()->units,
                unitPrice: $unitPriceNet,
                taxRate: $line->taxRate()
            ));
        }

        if ($format === InvoiceFormat::DIGITAL && $this->digitalEmitter) {
            $this->digitalEmitter->emit($invoice);
        }

        $this->invoices->save($invoice);
        return $invoice;
    }

    public function rectify(InvoiceId $original, CompanyIdentityId $issuer, string $seriesKey, InvoiceFormat $format): Invoice
    {
        $orig = $this->invoices->byId($original) ?? throw new \RuntimeException('Factura original no encontrada');
        $rect = new Invoice(
            new InvoiceId((string)\Ramsey\Uuid\Uuid::uuid4()),
            $this->sequencer->nextNumberFor($seriesKey),
            $this->clock->now(),
            InvoiceType::RECTIFICATION,
            $format,
            $issuer,
            $orig->saleId(),
            $orig->id()
        );
        $this->invoices->save($rect);
        return $rect;
    }
}
