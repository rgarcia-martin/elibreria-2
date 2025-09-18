<?php
// src/Infrastructure/Serialization/Normalizer/StockUnitNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Inventory\StockUnit;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class StockUnitNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ID          = 'id';
    public const KEY_ARTICLE_ID  = 'articleId';
    public const KEY_STATUS      = 'status';
    public const KEY_COST        = 'unitCost';
    public const KEY_LOCATION_ID = 'locationId';
    public const KEY_PROVIDER_ID = 'providerId';
    public const KEY_RECEIPT_ID  = 'goodsReceiptId';
    public const KEY_PROFIT      = 'profitSharing';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StockUnit;
    }

    /** @param StockUnit $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $profit = method_exists($object, 'profitSharing') ? $object->profitSharing() : $object->profitSharing;

        $receiptId = null;
        foreach (['goodsReceiptId', 'receiptId'] as $candidate) {
            if (method_exists($object, $candidate)) { $receiptId = (string)$object->{$candidate}(); break; }
            if (property_exists($object, $candidate)) { $receiptId = (string)$object->{$candidate}; break; }
        }

        $providerId = (string) (method_exists($object, 'providerId') ? $object->providerId() : $object->providerId);
        $locationId = (string) (method_exists($object, 'locationId') ? $object->locationId() : $object->locationId);

        return [
            self::KEY_ID          => (string) (method_exists($object, 'id') ? $object->id() : $object->id),
            self::KEY_ARTICLE_ID  => (string) (method_exists($object, 'articleId') ? $object->articleId() : $object->articleId),
            self::KEY_STATUS      => (method_exists($object, 'status') ? $object->status() : $object->status)->value,
            self::KEY_COST        => $this->normalizer->normalize(method_exists($object, 'unitCost') ? $object->unitCost() : $object->unitCost, $format, $context),
            self::KEY_LOCATION_ID => $locationId,
            self::KEY_PROVIDER_ID => $providerId,
            self::KEY_RECEIPT_ID  => $receiptId,
            self::KEY_PROFIT      => [
                'merchant' => $this->normalizer->normalize($profit->merchantShare(), $format, $context),
                'provider' => $this->normalizer->normalize($profit->providerShare(), $format, $context),
            ],
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [StockUnit::class => true];
    }
}
