<?php
// src/Domain/Sales/SalesService.php
declare(strict_types=1);

namespace App\Domain\Sales;

use App\Domain\Common\{Clock, Discount, Money, Quantity, Barcode};
use App\Domain\Catalog\ArticleRepository;
use App\Domain\Inventory\{StockRepository, StockUnitId};
use App\Domain\Pricing\StockSelectionPolicy;
use App\Domain\Ports\PaymentGatewayPort;

class SalesService
{
    public function __construct(
        private SaleRepository $sales,
        private ArticleRepository $articles,
        private StockRepository $stock,
        private StockSelectionPolicy $stockSelector,
        private PaymentGatewayPort $paymentGateway,
        private Clock $clock
    ) {}

    public function startSale(): Sale
    {
        $s = new Sale(new SaleId((string)\Ramsey\Uuid\Uuid::uuid4()), $this->clock->now());
        $this->sales->save($s);
        return $s;
    }

    public function addLineByBarcode(SaleId $saleId, Barcode $barcode, int $qty, Money $unitPrice, Discount $lineDiscount): void
    {
        $sale = $this->sales->byId($saleId) ?? throw new \RuntimeException('Venta no encontrada');
        $article = $this->articles->byBarcode($barcode) ?? throw new \RuntimeException('Artículo no encontrado');
        $line = new SaleLine(
            new SaleLineId((string)\Ramsey\Uuid\Uuid::uuid4()),
            $article->id(),
            new Quantity($qty),
            $unitPrice,
            $lineDiscount,
            $article->taxRate()
        );
        $sale->addLine($line);
        $this->sales->save($sale);
    }

    public function applyBestStock(SaleId $saleId): void
    {
        $sale = $this->sales->byId($saleId) ?? throw new \RuntimeException('Venta no encontrada');
        $assign = $this->stockSelector->selectFor($sale, $this->stock);
        foreach ($sale->lines() as $line) {
            $ids = $assign[(string)$line->id()] ?? [];
            $sale->assignStock($line->id(), $ids);
            $units = array_map(fn(string $sid)=>$this->stock->byId(new StockUnitId($sid)), $ids);
            $units = array_filter($units);
            foreach ($units as $u) $u->reserve();
            $this->stock->saveAll($units);
        }
        $this->sales->save($sale);
    }

    public function payWithCard(SaleId $saleId, Money $amount): string
    {
        $sale = $this->sales->byId($saleId) ?? throw new \RuntimeException('Venta no encontrada');
        $op = $this->paymentGateway->charge($amount);
        $sale->addPayment(new Payment($amount, PaymentMethod::CARD));
        $this->sales->save($sale);
        return $op;
    }

    public function payWithCash(SaleId $saleId, Money $amount): void
    {
        $sale = $this->sales->byId($saleId) ?? throw new \RuntimeException('Venta no encontrada');
        $sale->addPayment(new Payment($amount, PaymentMethod::CASH));
        $this->sales->save($sale);
    }

    public function close(SaleId $saleId): void
    {
        $sale = $this->sales->byId($saleId) ?? throw new \RuntimeException('Venta no encontrada');
        $sale->charge();
        $this->sales->save($sale);
    }
}
