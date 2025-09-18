<?php
// src/Infrastructure/Serialization/Normalizer/GoodsReceiptPhotoNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Inventory\GoodsReceiptPhoto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes GoodsReceiptPhoto into a lightweight array.
 */
final class GoodsReceiptPhotoNormalizer implements NormalizerInterface
{
    public const KEY_ID   = 'id';
    public const KEY_URI  = 'uri';
    public const KEY_MIME = 'mime';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GoodsReceiptPhoto;
    }

    /** @param GoodsReceiptPhoto $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            self::KEY_ID   => (string) $object->id(),
            self::KEY_URI  => method_exists($object, 'uri')  ? $object->uri()  : '',
            self::KEY_MIME => method_exists($object, 'mime') ? $object->mime() : '',
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [GoodsReceiptPhoto::class => true];
    }
}
