<?php
// src/Domain/Inventory/GoodsReceiptService.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Common\{Clock, Uuid, ProfitSharing, Quantity};
use App\Domain\Partners\ProviderId;
use App\Domain\Catalog\ArticleId;
use App\Domain\Common\Money;
use App\Domain\Locations\LocationId;

class GoodsReceiptService
{
    public function __construct(
        private GoodsReceiptRepository $receipts,
        private StockRepository $stock,
        private ConsignmentReturnRepository $returns,
        private Clock $clock
    ) {}

    /** @param array<int,array{uri:string,mime:string}> $photos */
    public function registerByPhotos(?ProviderId $provider, GoodsReceiptType $type, ?\DateTimeImmutable $returnDueAt, array $photos): GoodsReceipt
    {
        $gr = new GoodsReceipt(
            new GoodsReceiptId((string)\Ramsey\Uuid\Uuid::uuid4()),
            $type,
            $this->clock->now(),
            $provider,
            $returnDueAt
        );
        foreach ($photos as $p) {
            $gr->addPhoto(new GoodsReceiptPhoto(new Uuid((string)\Ramsey\Uuid\Uuid::uuid4()), $p['uri'], $p['mime']));
        }
        $this->receipts->save($gr);
        return $gr;
    }

    /**
     * @param array<int,array{articleId:ArticleId,qty:int,unitCost:Money,profitSharing:ProfitSharing,locationId?:LocationId}> $lines
     */
    public function registerStructured(?ProviderId $provider, GoodsReceiptType $type, ?\DateTimeImmutable $returnDueAt, array $lines): GoodsReceipt
    {
        $gr = new GoodsReceipt(
            new GoodsReceiptId((string)\Ramsey\Uuid\Uuid::uuid4()),
            $type,
            $this->clock->now(),
            $provider,
            $returnDueAt
        );

        foreach ($lines as $e) {
            $ps = $type === GoodsReceiptType::PURCHASED ? ProfitSharing::selfOwned() : $e['profitSharing'];
            $line = new GoodsReceiptLine($e['articleId'], new Quantity($e['qty']), $e['unitCost'], $ps, $e['locationId'] ?? null);
            $gr->addLine($line);

            for ($i=0; $i<$e['qty']; $i++) {
                $unit = new StockUnit(
                    new StockUnitId((string)\Ramsey\Uuid\Uuid::uuid4()),
                    $e['articleId'],
                    $gr->id(),
                    $provider,
                    $ps,
                    $e['unitCost'],
                    $e['locationId'] ?? new LocationId((string)\Ramsey\Uuid\Uuid::uuid4())
                );
                $this->stock->save($unit);
            }
        }
        $this->receipts->save($gr);
        return $gr;
    }

    public function bucketOf(GoodsReceipt $gr): ConsignmentBucket
    {
        if ($gr->type() === GoodsReceiptType::PURCHASED) return ConsignmentBucket::PURCHASED;
        $pending = $this->stock->notSoldUnitsOfReceipt($gr->id());
        if (count($pending) === 0) return ConsignmentBucket::CONSIGNMENT_RETURNED;

        $due = $gr->returnDueAt();
        if (!$due) return ConsignmentBucket::CONSIGNMENT_OK;
        return ($this->clock->now() > $due)
            ? ConsignmentBucket::CONSIGNMENT_OVERDUE
            : ConsignmentBucket::CONSIGNMENT_OK;
    }

    public function createFullReturn(GoodsReceiptId $receiptId): ConsignmentReturn
    {
        $gr = $this->receipts->byId($receiptId) ?? throw new \RuntimeException('Albarán no existe');
        if ($gr->type() !== GoodsReceiptType::CONSIGNMENT) throw new \LogicException('Solo consignación admite devolución');

        $pending = $this->stock->notSoldUnitsOfReceipt($receiptId);
        $ret = new ConsignmentReturn(
            new ConsignmentReturnId((string)\Ramsey\Uuid\Uuid::uuid4()),
            $receiptId,
            $this->clock->now()
        );
        $ret->includeUnits(array_map(fn(StockUnit $u)=>$u->id(), $pending));
        $this->returns->save($ret);
        return $ret;
    }

    public function confirmReturn(ConsignmentReturnId $returnId): void
    {
        $ret = $this->returns->byId($returnId) ?? throw new \RuntimeException('Devolución no existe');
        $units = $ret->units();
        $loaded = [];
        foreach ($units as $id) {
            $u = $this->stock->byId($id) ?? throw new \RuntimeException('Unidad no encontrada');
            $u->markReturned();
            $loaded[] = $u;
        }
        $this->stock->saveAll($loaded);
        $ret->confirm($this->clock->now());
        $this->returns->save($ret);
    }
}
