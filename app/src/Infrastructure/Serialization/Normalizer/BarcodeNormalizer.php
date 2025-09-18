<?php
// src/Infrastructure/Serialization/Normalizer/BarcodeNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Barcode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes Barcode VO into a string value without requiring __toString.
 */
final class BarcodeNormalizer implements NormalizerInterface
{
    public const KEY_VALUE = 'value';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Barcode;
    }

    /** @param Barcode $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            self::KEY_VALUE => (string) $object->value()
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Barcode::class => true];
    }
}
