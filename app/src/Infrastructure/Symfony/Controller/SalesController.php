<?php
// src/Infrastructure/Symfony/Controller/SalesController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Sales\{SalesService, SaleRepository, SaleId};
use App\Domain\Common\{Money, Discount, Percentage, Barcode};
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineSale, DoctrineSaleLine};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/sales')]
final class SalesController extends BaseApiController
{
    public function __construct(
        private SalesService $svc,
        private SaleRepository $repo,
        private EntityManagerInterface $em
    ) {}

    // CRUD listado
    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $rows = $this->em->createQueryBuilder()->select('s')->from(DoctrineSale::class,'s')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit'])
            ->getQuery()->getResult();
        $rows = array_map(fn($s)=>[
            'id'=>$s->id,'created_at'=>$s->created_at->format(DATE_ATOM),'status'=>$s->status
        ], $rows);
        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('', methods: ['POST'])]
    public function start(): JsonResponse
    {
        $s = $this->svc->startSale();
        return $this->jsonOk(['id'=>(string)$s->id(), 'createdAt'=>$s->createdAt()->format(DATE_ATOM)], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $s = $this->em->find(DoctrineSale::class, $id);
        if (!$s) return $this->jsonError('Not found',404);
        $lines = $this->em->getRepository(DoctrineSaleLine::class)->findBy(['sale_id'=>$id]);
        return $this->jsonOk([
            'id'=>$s->id,'created_at'=>$s->created_at->format(DATE_ATOM),'status'=>$s->status,
            'lines'=>array_map(fn($l)=>[
                'id'=>$l->id,'article_id'=>$l->article_id,'quantity'=>$l->quantity,
                'unit_price_amount'=>$l->unit_price_amount,'currency'=>$l->unit_price_currency,'tax_rate'=>$l->tax_rate
            ], $lines)
        ]);
    }

    // Edición: añadir líneas por código de barras
    #[Route('/{id}/scan', methods: ['POST'])]
    public function scan(string $id, Request $r): JsonResponse
    {
        $data = $this->body($r);
        $this->svc->addLineByBarcode(
            new SaleId($id),
            new Barcode((string)$data['barcode']),
            (int)($data['qty'] ?? 1),
            new Money((int)$data['unit_price_amount'], (string)($data['currency'] ?? 'EUR')),
            isset($data['discount_percent']) ? Discount::percent(new Percentage((float)$data['discount_percent'])) :
                (isset($data['discount_fixed_amount']) ? Discount::fixed(new Money((int)$data['discount_fixed_amount'], (string)($data['currency'] ?? 'EUR'))) : Discount::none())
        );
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}/apply-best-stock', methods: ['POST'])]
    public function applyBestStock(string $id): JsonResponse
    {
        $this->svc->applyBestStock(new SaleId($id));
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}/pay', methods: ['POST'])]
    public function pay(string $id, Request $r): JsonResponse
    {
        $d = $this->body($r);
        $amount = new Money((int)$d['amount'], (string)($d['currency'] ?? 'EUR'));
        if (($d['method'] ?? 'cash') === 'card') {
            $op = $this->svc->payWithCard(new SaleId($id), $amount);
            return $this->jsonOk(['ok'=>true,'opId'=>$op]);
        }
        $this->svc->payWithCash(new SaleId($id), $amount);
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}/close', methods: ['POST'])]
    public function close(string $id): JsonResponse
    {
        $this->svc->close(new SaleId($id));
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        // borrar venta solo si está en borrador (seguridad a nivel de dominio no implementada aquí)
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineSale::class, $id)]);
    }
}
