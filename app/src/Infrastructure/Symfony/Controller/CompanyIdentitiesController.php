<?php
// src/Infrastructure/Symfony/Controller/CompanyIdentitiesController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Identity\{CompanyIdentity, CompanyIdentityId, CompanyIdentityRepository};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineCompanyIdentity;
use App\Infrastructure\Persistence\Doctrine\Mapper\CompanyIdentityMapper;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/company-identities')]
final class CompanyIdentitiesController extends BaseApiController
{
    public function __construct(
        private CompanyIdentityRepository $repo,
        private CompanyIdentityMapper $map,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $rows = $this->em->createQueryBuilder()->select('c')->from(DoctrineCompanyIdentity::class,'c')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit'])->getQuery()->getResult();
        $rows = array_map(fn($c)=>[
            'id'=>$c->id,'legal_name'=>$c->legal_name,'tax_id'=>$c->tax_id,
            'address'=>$c->address,'e_invoicing_id'=>$c->e_invoicing_id
        ], $rows);
        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $c = new CompanyIdentity(
            new CompanyIdentityId(Uuid::uuid4()->toString()),
            (string)$d['legal_name'], (string)$d['tax_id'], (string)$d['address'],
            $d['e_invoicing_id'] ?? null
        );
        $this->repo->save($c);
        return $this->jsonOk(['id'=>(string)$c->id()], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $e = $this->em->find(DoctrineCompanyIdentity::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        return $this->jsonOk([
            'id'=>$e->id,'legal_name'=>$e->legal_name,'tax_id'=>$e->tax_id,
            'address'=>$e->address,'e_invoicing_id'=>$e->e_invoicing_id
        ]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    public function update(string $id, Request $r): JsonResponse
    {
        $e = $this->em->find(DoctrineCompanyIdentity::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        $d = $this->body($r);
        foreach (['legal_name','tax_id','address','e_invoicing_id'] as $k) if (array_key_exists($k,$d)) $e->$k = $d[$k] ?: null;
        $this->em->flush();
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineCompanyIdentity::class, $id)]);
    }
}
