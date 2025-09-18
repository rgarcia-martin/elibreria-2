<?php
// src/Domain/Sales/Sale.php
declare(strict_types=1);

namespace App\Domain\Sales;

use App\Domain\Common\{Money, Discount};

class Sale
{
    private SaleId $id;
    private \DateTimeImmutable $createdAt;
    private SaleStatus $status;
    private Discount $globalDiscount;

    /** @var SaleLine[] */
    private array $lines = [];
    /** @var Payment[] */
    private array $payments = [];
    /** @var array<string, string[]> lineId(string) => StockUnitId(string)[] */
    private array $assignments = [];

    public function __construct(
        SaleId $id,
        \DateTimeImmutable $createdAt,
        SaleStatus $status = SaleStatus::DRAFT,
        ?Discount $globalDiscount = null
    ) {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->status = $status;
        $this->globalDiscount = $globalDiscount ?? Discount::none();
    }

    public function id(): SaleId { return $this->id; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function status(): SaleStatus { return $this->status; }
    public function globalDiscount(): Discount { return $this->globalDiscount; }

    public function addLine(SaleLine $l): void { $this->lines[] = $l; }
    /** @return SaleLine[] */ public function lines(): array { return $this->lines; }

    public function assignStock(SaleLineId $lineId, array $unitIds): void
    {
        $this->assignments[(string)$lineId] = array_map('strval', $unitIds);
    }

    public function addPayment(Payment $p): void { $this->payments[] = $p; }

    public function totalBeforeDiscounts(): Money
    {
        $sum = Money::zero();
        foreach ($this->lines as $l) {
            $sum = $sum->add($l->totalBeforeDiscount());
        }
        return $sum;
    }

    public function totalAfterDiscounts(): Money
    {
        $sum = Money::zero();
        foreach ($this->lines as $l) {
            $sum = $sum->add($l->totalAfterDiscount());
        }
        return $this->globalDiscount->apply($sum);
    }

    public function charge(): void
    {
        if ($this->status !== SaleStatus::DRAFT) {
            throw new \LogicException('Venta ya cobrada');
        }
        $paid = Money::zero();
        foreach ($this->payments as $p) {
            $paid = $paid->add($p->amount);
        }
        if ($paid->amount < $this->totalAfterDiscounts()->amount) {
            throw new \LogicException('Pago insuficiente');
        }
        $this->status = SaleStatus::CHARGED;
    }
}
