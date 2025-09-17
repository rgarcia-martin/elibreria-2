<?php
// src/Infrastructure/Symfony/Controller/ArticlesController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Catalog\{Article, ArticleId, ArticleRepository};
use App\Domain\Common\{Barcode, Money, Percentage};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineArticle;
use App\Infrastructure\Persistence\Doctrine\Mapper\ArticleMapper;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/articles')]
final class ArticlesController extends BaseApiController
{
    public function __construct(
        private ArticleRepository $repo,
        private ArticleMapper $map,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $qb = $this->em->createQueryBuilder()
            ->select('a')->from(DoctrineArticle::class, 'a')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit']);

        if ($b = $r->query->get('barcode')) {
            $qb->andWhere('a.barcode = :b')->setParameter('b', $b);
        }
        $rows = array_map(fn($e) => [
            'id' => $e->id, 'name' => $e->name, 'barcode' => $e->barcode,
            'base_price_amount' => $e->base_price_amount, 'currency' => $e->base_price_currency,
            'tax_rate' => $e->tax_rate
        ], $qb->getQuery()->getResult());

        return $this->jsonOk(['items' => $rows, 'limit' => $p['limit'], 'offset' => $p['offset']]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $a = new Article(
            new ArticleId(Uuid::uuid4()->toString()),
            (string)$d['name'],
            isset($d['barcode']) && $d['barcode'] !== '' ? new Barcode((string)$d['barcode']) : null,
            new Money((int)$d['base_price_amount'], (string)($d['currency'] ?? 'EUR')),
            new Percentage((float)$d['tax_rate'])
        );
        $this->repo->save($a);
        return $this->jsonOk(['id' => (string)$a->id()], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $a = $this->repo->byId(new ArticleId($id));
        if (!$a) return $this->jsonError('Not found', 404);
        return $this->jsonOk([
            'id' => (string)$a->id(), 'name' => $a->name(),
            'barcode' => $a->barcode()?->value,
            'base_price_amount' => $a->basePrice()->amount,
            'currency' => $a->basePrice()->currency,
            'tax_rate' => $a->taxRate()->value
        ]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    public function update(string $id, Request $r): JsonResponse
    {
        $existing = $this->em->find(DoctrineArticle::class, $id);
        if (!$existing) return $this->jsonError('Not found', 404);
        $d = $this->body($r);
        $existing->name = $d['name'] ?? $existing->name;
        $existing->barcode = array_key_exists('barcode', $d) ? ($d['barcode'] ?: null) : $existing->barcode;
        if (isset($d['base_price_amount'])) $existing->base_price_amount = (int)$d['base_price_amount'];
        if (isset($d['currency'])) $existing->base_price_currency = (string)$d['currency'];
        if (isset($d['tax_rate'])) $existing->tax_rate = (float)$d['tax_rate'];
        $this->em->flush();
        return $this->jsonOk(['ok' => true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok' => $this->deleteById($this->em, DoctrineArticle::class, $id)]);
    }
}
