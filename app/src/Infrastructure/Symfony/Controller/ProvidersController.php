<?php
// src/Infrastructure/Symfony/Controller/ProvidersController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Partners\{Provider, ProviderId, ProviderContact, ProviderRepository};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineProvider;
use App\Infrastructure\Persistence\Doctrine\Mapper\ProviderMapper;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/providers')]
final class ProvidersController extends BaseApiController
{
    public function __construct(
        private ProviderRepository $repo,
        private ProviderMapper $map,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $qb = $this->em->createQueryBuilder()->select('p')->from(DoctrineProvider::class, 'p')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit']);

        if ($name = $r->query->get('name')) {
            $qb->andWhere('p.name LIKE :n')->setParameter('n', '%'.$name.'%');
        }
        $rows = array_map(fn($e) => [
            'id'=>$e->id,'name'=>$e->name,'tax_id'=>$e->tax_id,'email'=>$e->email,
            'phone'=>$e->phone,'address'=>$e->address,'default_consignment_days'=>$e->default_consignment_days
        ], $qb->getQuery()->getResult());
        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $prov = new Provider(
            new ProviderId(Uuid::uuid4()->toString()),
            (string)$d['name'],
            $d['tax_id'] ?? null,
            new ProviderContact((string)$d['email'], $d['phone'] ?? null, $d['address'] ?? null),
            isset($d['default_consignment_days']) ? (int)$d['default_consignment_days'] : null
        );
        $this->repo->save($prov);
        return $this->jsonOk(['id'=>(string)$prov->id()], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $e = $this->em->find(DoctrineProvider::class, $id);
        if (!$e) return $this->jsonError('Not found', 404);
        return $this->jsonOk([
            'id'=>$e->id,'name'=>$e->name,'tax_id'=>$e->tax_id,'email'=>$e->email,
            'phone'=>$e->phone,'address'=>$e->address,'default_consignment_days'=>$e->default_consignment_days
        ]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    public function update(string $id, Request $r): JsonResponse
    {
        $e = $this->em->find(DoctrineProvider::class, $id);
        if (!$e) return $this->jsonError('Not found', 404);
        $d = $this->body($r);
        foreach (['name','tax_id','email','phone','address'] as $k) if (array_key_exists($k,$d)) $e->$k = $d[$k] ?: null;
        if (array_key_exists('default_consignment_days',$d)) $e->default_consignment_days = $d['default_consignment_days']!==null ? (int)$d['default_consignment_days'] : null;
        $this->em->flush();
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineProvider::class, $id)]);
    }
}
