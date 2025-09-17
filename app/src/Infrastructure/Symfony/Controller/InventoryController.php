<?php
// src/Infrastructure/Symfony/Controller/InventoryController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Inventory\{GoodsReceiptService, GoodsReceiptType, GoodsReceiptId, ConsignmentBucket};
use App\Domain\Partners\ProviderId;
use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Money, Percentage, ProfitSharing, Quantity};
use App\Domain\Locations\LocationId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/receipts')]
final class InventoryController
{
    public function __construct(private GoodsReceiptService $svc) {}

    #[Route('/photos', name: 'receipts_by_photos', methods: ['POST'])]
    public function registerByPhotos(Request $r): JsonResponse
    {
        $d = json_decode($r->getContent(), true) ?? [];
        $gr = $this->svc->registerByPhotos(
            isset($d['provider_id']) ? new ProviderId($d['provider_id']) : null,
            GoodsReceiptType::from($d['type']),
            isset($d['return_due_at']) ? new \DateTimeImmutable($d['return_due_at']) : null,
            $d['photos'] ?? []
        );
        return new JsonResponse(['id'=>(string)$gr->id()]);
    }

    #[Route('', name: 'receipts_structured', methods: ['POST'])]
    public function registerStructured(Request $r): JsonResponse
    {
        $d = json_decode($r->getContent(), true) ?? [];
        $lines = array_map(function(array $l) {
            return [
                'articleId' => new ArticleId($l['article_id']),
                'qty' => (int)$l['qty'],
                'unitCost' => new Money((int)$l['unit_cost_amount'], $l['currency'] ?? 'EUR'),
                'profitSharing' => isset($l['merchant_share'])
                    ? new ProfitSharing(new Percentage((float)$l['merchant_share']), new Percentage((float)$l['provider_share'] ?? 0.0))
                    : ProfitSharing::selfOwned(),
                'locationId' => isset($l['location_id']) ? new LocationId($l['location_id']) : null,
            ];
        }, $d['lines'] ?? []);
        $gr = $this->svc->registerStructured(
            isset($d['provider_id']) ? new ProviderId($d['provider_id']) : null,
            GoodsReceiptType::from($d['type']),
            isset($d['return_due_at']) ? new \DateTimeImmutable($d['return_due_at']) : null,
            $lines
        );
        return new JsonResponse(['id'=>(string)$gr->id()]);
    }
}
