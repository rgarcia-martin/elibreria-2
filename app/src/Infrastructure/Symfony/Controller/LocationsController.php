<?php
// src/Infrastructure/Symfony/Controller/LocationsController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Locations\{Location, LocationId, LocationRepository};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineLocation;
use App\Infrastructure\Persistence\Doctrine\Mapper\LocationMapper;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/locations')]
final class LocationsController extends BaseApiController
{
    public function __construct(
        private LocationRepository $repo,
        private LocationMapper $map,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $rows = $this->em->createQueryBuilder()->select('l')->from(DoctrineLocation::class,'l')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit'])
            ->getQuery()->getResult();
        $rows = array_map(fn($l)=>['id'=>$l->id,'name'=>$l->name,'parent_id'=>$l->parent_id], $rows);
        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $loc = new Location(
            new LocationId(Uuid::uuid4()->toString()),
            (string)$d['name'],
            isset($d['parent_id']) ? new LocationId((string)$d['parent_id']) : null
        );
        $this->repo->save($loc);
        return $this->jsonOk(['id'=>(string)$loc->id()], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $e = $this->em->find(DoctrineLocation::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        return $this->jsonOk(['id'=>$e->id,'name'=>$e->name,'parent_id'=>$e->parent_id]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    public function update(string $id, Request $r): JsonResponse
    {
        $e = $this->em->find(DoctrineLocation::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        $d = $this->body($r);
        if (array_key_exists('name',$d)) $e->name = (string)$d['name'];
        if (array_key_exists('parent_id',$d)) $e->parent_id = $d['parent_id'] ?: null;
        $this->em->flush();
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineLocation::class, $id)]);
    }
}
