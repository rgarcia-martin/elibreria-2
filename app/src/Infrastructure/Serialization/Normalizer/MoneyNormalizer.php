<?php
// src/Infrastructure/Serialization/Normalizer/MoneyNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Money;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Normalizes Money value object into a scalar array.
 */
final class MoneyNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_AMOUNT   = 'amount';
    public const KEY_CURRENCY = 'currency';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Money;
    }

    /** @param Money $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            self::KEY_AMOUNT   => $object->amount,
            self::KEY_CURRENCY => $object->currency,
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Money::class => true];
    }
}
