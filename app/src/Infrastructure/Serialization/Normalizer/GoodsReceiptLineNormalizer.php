<?php
// src/Infrastructure/Serialization/Normalizer/GoodsReceiptLineNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Inventory\GoodsReceiptLine;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Normalizes GoodsReceiptLine.
 */
final class GoodsReceiptLineNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ARTICLEID     = 'articleId';
    public const KEY_QTY           = 'quantity';
    public const KEY_UNIT_COST     = 'unitCost';
    public const KEY_PROFIT_SHARE  = 'profitSharing';
    public const KEY_LOCATION_ID   = 'locationId';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GoodsReceiptLine;
    }

    /** @param GoodsReceiptLine $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            self::KEY_ARTICLEID    => (string) $object->articleId(),
            self::KEY_QTY          => $object->quantity()->value,
            self::KEY_UNIT_COST    => $this->normalizer->normalize($object->unitCost(), $format, $context),
            self::KEY_PROFIT_SHARE => [
                'merchant' => $this->normalizer->normalize($object->profitSharing()->merchantShare(), $format, $context),
                'provider' => $this->normalizer->normalize($object->profitSharing()->providerShare(), $format, $context),
            ],
            self::KEY_LOCATION_ID  => (string) $object->locationId(),
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [GoodsReceiptLine::class => true];
    }
}
