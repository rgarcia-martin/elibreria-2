<?php
// src/Infrastructure/Serialization/Normalizer/SaleNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Sales\Sale;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class SaleNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ID           = 'id';
    public const KEY_CREATED_AT   = 'createdAt';
    public const KEY_STATUS       = 'status';
    public const KEY_GLOBAL_DISC  = 'globalDiscount';
    public const KEY_LINES        = 'lines';
    public const KEY_SUBTOTAL     = 'subtotal';
    public const KEY_TOTAL        = 'total';
    public const DATE_FORMAT      = \DateTimeInterface::ATOM;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Sale;
    }

    /** @param Sale $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $created = $this->firstValue($object, ['createdAt', 'date', 'occurredAt', 'createdOn']);
        $status  = method_exists($object, 'status') ? $object->status() : null;

        $globalDiscount = $this->firstValue($object, ['globalDiscount', 'discount']);
        $lines          = $this->firstValue($object, ['lines']);
        $subtotal       = $this->firstValue($object, ['totalBeforeDiscounts', 'subtotal']);
        $total          = $this->firstValue($object, ['totalAfterDiscounts', 'total']);

        return [
            self::KEY_ID          => (string) (method_exists($object, 'id') ? $object->id() : $object->id),
            self::KEY_CREATED_AT  => $created instanceof \DateTimeInterface ? $created->format(self::DATE_FORMAT) : null,
            self::KEY_STATUS      => is_object($status) && property_exists($status, 'value') ? $status->value : ($status?->value ?? (string)$status),
            self::KEY_GLOBAL_DISC => $globalDiscount ? $this->normalizer->normalize($globalDiscount, $format, $context) : null,
            self::KEY_LINES       => $this->normalizer->normalize($lines ?? [], $format, $context),
            self::KEY_SUBTOTAL    => $subtotal ? $this->normalizer->normalize($subtotal, $format, $context) : null,
            self::KEY_TOTAL       => $total ? $this->normalizer->normalize($total, $format, $context) : null,
        ];
    }

    private function firstValue(object $o, array $candidates): mixed
    {
        foreach ($candidates as $name) {
            if (method_exists($o, $name)) {
                return $o->{$name}();
            }
            if (property_exists($o, $name)) {
                return $o->{$name};
            }
        }
        return null;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Sale::class => true];
    }
}
