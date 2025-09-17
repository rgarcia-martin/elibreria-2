<?php
// src/Infrastructure/Symfony/Controller/StockUnitsController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineStockUnit;
use App\Domain\Locations\LocationId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/stock-units')]
final class StockUnitsController extends BaseApiController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $qb = $this->em->createQueryBuilder()->select('s')->from(DoctrineStockUnit::class,'s')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit']);

        foreach (['article_id','origin_receipt_id','provider_id','location_id','status'] as $f) {
            if ($v = $r->query->get($f)) $qb->andWhere("s.$f = :$f")->setParameter($f, $v);
        }
        $rows = array_map(fn($e)=>[
            'id'=>$e->id,'article_id'=>$e->article_id,'origin_receipt_id'=>$e->origin_receipt_id,
            'provider_id'=>$e->provider_id,'merchant_share'=>$e->merchant_share,'provider_share'=>$e->provider_share,
            'unit_cost_amount'=>$e->unit_cost_amount,'currency'=>$e->unit_cost_currency,
            'location_id'=>$e->location_id,'status'=>$e->status,'sold_at'=>$e->sold_at?->format(DATE_ATOM)
        ], $qb->getQuery()->getResult());

        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $e = $this->em->find(DoctrineStockUnit::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        return $this->jsonOk([
            'id'=>$e->id,'article_id'=>$e->article_id,'origin_receipt_id'=>$e->origin_receipt_id,
            'provider_id'=>$e->provider_id,'merchant_share'=>$e->merchant_share,'provider_share'=>$e->provider_share,
            'unit_cost_amount'=>$e->unit_cost_amount,'currency'=>$e->unit_cost_currency,
            'location_id'=>$e->location_id,'status'=>$e->status,'sold_at'=>$e->sold_at?->format(DATE_ATOM)
        ]);
    }

    #[Route('/{id}/move', methods: ['PATCH'])]
    public function move(string $id, Request $r): JsonResponse
    {
        $e = $this->em->find(DoctrineStockUnit::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        $d = $this->body($r);
        if (empty($d['location_id'])) return $this->jsonError('location_id requerido', 422);
        $e->location_id = (string)$d['location_id'];
        $this->em->flush();
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        // normalmenteno se borra stock histórico; lo dejamos 405
        return $this->jsonError('Method not allowed', 405);
    }
}
