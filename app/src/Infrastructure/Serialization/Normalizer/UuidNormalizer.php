<?php
// src/Infrastructure/Serialization/Normalizer/UuidNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes Domain Uuid into a string.
 */
final class UuidNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Uuid;
    }

    /** @param Uuid $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Uuid::class => true];
    }
}
