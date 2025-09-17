<?php
// src/Infrastructure/Symfony/Controller/UsersController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Users\{User, UserId, UserRepository, Role};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use App\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/users')]
final class UsersController extends BaseApiController
{
    public function __construct(
        private UserRepository $repo,
        private UserMapper $map,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $rows = $this->em->createQueryBuilder()->select('u')->from(DoctrineUser::class,'u')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit'])->getQuery()->getResult();
        $rows = array_map(fn($u)=>['id'=>$u->id,'email'=>$u->email,'roles'=>$u->roles], $rows);
        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $roles = array_map(fn($v)=>Role::from((string)$v), (array)($d['roles'] ?? ['ROLE_CASHIER']));
        $user = new User(
            new UserId(Uuid::uuid4()->toString()),
            (string)$d['email'],
            (string)($d['password_hash'] ?? ''), // en prod: hashea
            $roles
        );
        $this->repo->save($user);
        return $this->jsonOk(['id'=>(string)$user->id()], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $e = $this->em->find(DoctrineUser::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        return $this->jsonOk(['id'=>$e->id,'email'=>$e->email,'roles'=>$e->roles]);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    public function update(string $id, Request $r): JsonResponse
    {
        $e = $this->em->find(DoctrineUser::class, $id);
        if (!$e) return $this->jsonError('Not found',404);
        $d = $this->body($r);
        if (array_key_exists('email',$d)) $e->email = (string)$d['email'];
        if (array_key_exists('password_hash',$d)) $e->password_hash = (string)$d['password_hash']; // en prod: hashea
        if (array_key_exists('roles',$d)) $e->roles = array_values((array)$d['roles']);
        $this->em->flush();
        return $this->jsonOk(['ok'=>true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineUser::class, $id)]);
    }
}
