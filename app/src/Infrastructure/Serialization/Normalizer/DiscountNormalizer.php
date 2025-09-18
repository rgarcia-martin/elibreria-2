<?php
// src/Infrastructure/Serialization/Normalizer/DiscountNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Discount;
use App\Domain\Common\Money;
use App\Domain\Common\Percentage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Normalizes Discount without assuming specific domain methods.
 */
final class DiscountNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_TYPE   = 'type';
    public const KEY_VALUE  = 'value';
    public const TYPE_NONE  = 'none';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_RATE  = 'percent';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Discount;
    }

    /** @param Discount $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        // 1) Explicit “is*” methods if present
        if (method_exists($object, 'isNone') && $object->isNone()) {
            return [self::KEY_TYPE => self::TYPE_NONE];
        }
        if (method_exists($object, 'isFixed') && $object->isFixed()) {
            $fixed = method_exists($object, 'fixed') ? $object->fixed() : null;
            return [
                self::KEY_TYPE  => self::TYPE_FIXED,
                self::KEY_VALUE => $fixed instanceof Money ? $this->normalizer->normalize($fixed, $format, $context) : null,
            ];
        }
        if (method_exists($object, 'isPercent') && $object->isPercent()) {
            $rate = method_exists($object, 'percent') ? $object->percent() : null;
            return [
                self::KEY_TYPE  => self::TYPE_RATE,
                self::KEY_VALUE => $rate instanceof Percentage ? $this->normalizer->normalize($rate, $format, $context) : null,
            ];
        }

        // 2) Heuristic: presence of accessors/properties
        $fixed = method_exists($object, 'fixed') ? $object->fixed() : (property_exists($object, 'fixed') ? $object->fixed : null);
        if ($fixed instanceof Money) {
            return [
                self::KEY_TYPE  => self::TYPE_FIXED,
                self::KEY_VALUE => $this->normalizer->normalize($fixed, $format, $context),
            ];
        }

        $rate = method_exists($object, 'percent') ? $object->percent() : (property_exists($object, 'percent') ? $object->percent : null);
        if ($rate instanceof Percentage) {
            return [
                self::KEY_TYPE  => self::TYPE_RATE,
                self::KEY_VALUE => $this->normalizer->normalize($rate, $format, $context),
            ];
        }

        return [self::KEY_TYPE => self::TYPE_NONE];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Discount::class => true];
    }
}
