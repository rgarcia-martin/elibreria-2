<?php
// src/Infrastructure/Symfony/Controller/BaseApiController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseApiController
{
    protected function jsonOk(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function jsonError(string $message, int $status = 400): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }

    protected function body(Request $r): array
    {
        return json_decode($r->getContent() ?: '[]', true) ?? [];
    }

    /** @return array{limit:int,offset:int} */
    protected function pagination(Request $r, int $defaultLimit = 20): array
    {
        $limit = max(1, (int)($r->query->get('limit') ?? $defaultLimit));
        $offset = max(0, (int)($r->query->get('offset') ?? 0));
        return ['limit' => $limit, 'offset' => $offset];
    }

    protected function deleteById(EntityManagerInterface $em, string $class, string $id): bool
    {
        $entity = $em->find($class, $id);
        if (!$entity) return false;
        $em->remove($entity);
        $em->flush();
        return true;
    }
}
