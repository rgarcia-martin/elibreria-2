<?php
// src/Infrastructure/Serialization/JsonApiResponder.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Thin HTTP JSON responder leveraging Symfony Serializer + our domain normalizers.
 */
final class JsonApiResponder
{
    public const HEADER_CT     = 'Content-Type';
    public const MIME_JSON     = 'application/json';
    public const DEFAULT_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function __construct(private readonly SerializerInterface $serializer) {}

    public function ok(mixed $data, int $status = JsonResponse::HTTP_OK, array $headers = []): JsonResponse
    {
        return $this->json($data, $status, $headers);
    }

    public function created(mixed $data, array $headers = []): JsonResponse
    {
        return $this->json($data, JsonResponse::HTTP_CREATED, $headers);
    }

    public function noContent(array $headers = []): JsonResponse
    {
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT, $headers);
    }

    private function json(mixed $data, int $status, array $headers): JsonResponse
    {
        $context = [
            JsonResponse::DEFAULT_ENCODING_OPTIONS => self::DEFAULT_FLAGS,
            // add circular reference handler or max depth if needed
        ];

        $json = $this->serializer->serialize($data, 'json', $context);

        $headers[self::HEADER_CT] = self::MIME_JSON;

        // $json is already encoded
        return new JsonResponse($json, $status, $headers, true);
    }
}
