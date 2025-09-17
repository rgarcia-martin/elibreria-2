<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/GoodsReceiptMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Inventory\{
    GoodsReceipt, GoodsReceiptId, GoodsReceiptLine, GoodsReceiptPhoto,
    GoodsReceiptType, GoodsReceiptState
};
use App\Domain\Partners\ProviderId;
use App\Domain\Catalog\ArticleId;
use App\Domain\Locations\LocationId;
use App\Domain\Common\{Money, ProfitSharing, Quantity, Uuid, Percentage};
use App\Infrastructure\Persistence\Doctrine\Entity\{
    DoctrineGoodsReceipt, DoctrineGoodsReceiptLine, DoctrineGoodsReceiptPhoto
};

final class GoodsReceiptMapper
{
    /** @return DoctrineGoodsReceipt */
    public function toEntity(GoodsReceipt $gr): DoctrineGoodsReceipt
    {
        $e = new DoctrineGoodsReceipt();
        $e->id = (string)$gr->id();
        $e->type = $gr->type()->value;
        $e->received_at = $gr->receivedAt();
        $e->provider_id = $gr->providerId()?->value;
        $e->return_due_at = $gr->returnDueAt();
        $e->state = GoodsReceiptState::OPEN->value; // simplificado
        $e->lines = [];
        foreach ($gr->lines() as $line) {
            $el = new DoctrineGoodsReceiptLine();
            $el->id = (string)new Uuid(\Ramsey\Uuid\Uuid::uuid4()->toString());
            $el->receipt = $e;
            $el->article_id = (string)$line->articleId();
            $el->quantity = $line->quantity()->units;
            $el->unit_cost_amount = $line->unitCost()->amount;
            $el->unit_cost_currency = $line->unitCost()->currency;
            $el->merchant_share = $line->profitSharing()->merchantShare->value;
            $el->provider_share = $line->profitSharing()->providerShare->value;
            $el->initial_location_id = $line->initialLocation()?->value;
            $e->lines[] = $el;
        }
        $e->photos = [];
        foreach ($gr->photos() as $p) {
            $ep = new DoctrineGoodsReceiptPhoto();
            $ep->id = (string)$p->id->value;
            $ep->receipt = $e;
            $ep->uri = $p->uri;
            $ep->mime = $p->mime;
            $e->photos[] = $ep;
        }
        return $e;
    }

    public function toDomain(DoctrineGoodsReceipt $e): GoodsReceipt
    {
        $gr = new GoodsReceipt(
            new GoodsReceiptId($e->id),
            GoodsReceiptType::from($e->type),
            $e->received_at,
            $e->provider_id ? new ProviderId($e->provider_id) : null,
            $e->return_due_at
        );
        foreach ($e->lines ?? [] as $el) {
            $gr->addLine(new GoodsReceiptLine(
                new ArticleId($el->article_id),
                new Quantity($el->quantity),
                new Money($el->unit_cost_amount, $el->unit_cost_currency),
                new ProfitSharing(new Percentage($el->merchant_share), new Percentage($el->provider_share)),
                $el->initial_location_id ? new LocationId($el->initial_location_id) : null
            ));
        }
        foreach ($e->photos ?? [] as $ep) {
            $gr->addPhoto(new GoodsReceiptPhoto(new Uuid($ep->id), $ep->uri, $ep->mime));
        }
        return $gr;
    }
}
