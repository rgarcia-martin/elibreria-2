<?php
// src/Infrastructure/Symfony/Controller/GoodsReceiptsController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Inventory\{
    GoodsReceiptService, GoodsReceiptType, GoodsReceiptId, ConsignmentBucket
};
use App\Domain\Partners\ProviderId;
use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Money, Percentage, ProfitSharing, Quantity};
use App\Domain\Locations\LocationId;
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineGoodsReceipt, DoctrineGoodsReceiptLine, DoctrineGoodsReceiptPhoto};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/receipts')]
final class GoodsReceiptsController extends BaseApiController
{
    public function __construct(
        private GoodsReceiptService $svc,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $qb = $this->em->createQueryBuilder()->select('r')->from(DoctrineGoodsReceipt::class,'r')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit']);

        if ($t = $r->query->get('type')) $qb->andWhere('r.type = :t')->setParameter('t',(string)$t);
        if ($pid = $r->query->get('provider_id')) $qb->andWhere('r.provider_id = :p')->setParameter('p',(string)$pid);

        $rows = array_map(fn($e)=>[
            'id'=>$e->id,'type'=>$e->type,'received_at'=>$e->received_at->format(DATE_ATOM),
            'provider_id'=>$e->provider_id,'return_due_at'=>$e->return_due_at?->format(DATE_ATOM),'state'=>$e->state
        ], $qb->getQuery()->getResult());

        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $e = $this->em->find(DoctrineGoodsReceipt::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        $lines = $this->em->getRepository(DoctrineGoodsReceiptLine::class)->findBy(['receipt'=>$e]);
        $photos = $this->em->getRepository(DoctrineGoodsReceiptPhoto::class)->findBy(['receipt'=>$e]);
        return $this->jsonOk([
            'id'=>$e->id,'type'=>$e->type,'received_at'=>$e->received_at->format(DATE_ATOM),
            'provider_id'=>$e->provider_id,'return_due_at'=>$e->return_due_at?->format(DATE_ATOM),'state'=>$e->state,
            'lines'=>array_map(fn($l)=>[
                'id'=>$l->id,'article_id'=>$l->article_id,'quantity'=>$l->quantity,
                'unit_cost_amount'=>$l->unit_cost_amount,'currency'=>$l->unit_cost_currency,
                'merchant_share'=>$l->merchant_share,'provider_share'=>$l->provider_share,
                'initial_location_id'=>$l->initial_location_id
            ], $lines),
            'photos'=>array_map(fn($p)=>['id'=>$p->id,'uri'=>$p->uri,'mime'=>$p->mime], $photos)
        ]);
    }

    // Casos de uso mínimos ya existentes
    #[Route('/photos', methods: ['POST'])]
    public function registerByPhotos(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $gr = $this->svc->registerByPhotos(
            isset($d['provider_id']) ? new ProviderId($d['provider_id']) : null,
            GoodsReceiptType::from($d['type']),
            isset($d['return_due_at']) ? new \DateTimeImmutable((string)$d['return_due_at']) : null,
            $d['photos'] ?? []
        );
        return $this->jsonOk(['id'=>(string)$gr->id()], 201);
    }

    #[Route('', methods: ['POST'])]
    public function registerStructured(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $lines = array_map(function(array $l) {
            return [
                'articleId' => new ArticleId($l['article_id']),
                'qty' => (int)$l['qty'],
                'unitCost' => new Money((int)$l['unit_cost_amount'], $l['currency'] ?? 'EUR'),
                'profitSharing' => isset($l['merchant_share'])
                    ? new ProfitSharing(new Percentage((float)$l['merchant_share']), new Percentage((float)($l['provider_share'] ?? 0)))
                    : ProfitSharing::selfOwned(),
                'locationId' => isset($l['location_id']) ? new LocationId($l['location_id']) : null,
            ];
        }, $d['lines'] ?? []);
        $gr = $this->svc->registerStructured(
            isset($d['provider_id']) ? new ProviderId($d['provider_id']) : null,
            GoodsReceiptType::from($d['type']),
            isset($d['return_due_at']) ? new \DateTimeImmutable((string)$d['return_due_at']) : null,
            $lines
        );
        return $this->jsonOk(['id'=>(string)$gr->id()], 201);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineGoodsReceipt::class, $id)]);
    }
}
