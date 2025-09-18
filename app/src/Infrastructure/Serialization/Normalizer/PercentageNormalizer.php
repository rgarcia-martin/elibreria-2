<?php
// src/Infrastructure/Serialization/Normalizer/PercentageNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Percentage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes Percentage value object into a float.
 */
final class PercentageNormalizer implements NormalizerInterface
{
    public const KEY_VALUE = 'value';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Percentage;
    }

    /** @param Percentage $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): float
    {
        return $object->value;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Percentage::class => true];
    }
}
