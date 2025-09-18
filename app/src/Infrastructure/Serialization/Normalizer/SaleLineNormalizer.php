<?php
// src/Infrastructure/Serialization/Normalizer/SaleLineNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Sales\SaleLine;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Tolerant normalizer for SaleLine.
 */
final class SaleLineNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ID        = 'id';
    public const KEY_ARTICLEID = 'articleId';
    public const KEY_QUANTITY  = 'quantity';
    public const KEY_UNITPRICE = 'unitPrice';
    public const KEY_DISCOUNT  = 'discount';
    public const KEY_TAX       = 'taxRate';
    public const KEY_TOTAL     = 'total';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SaleLine;
    }

    /** @param SaleLine $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $qtyObj = method_exists($object, 'quantity') ? $object->quantity() : (property_exists($object, 'quantity') ? $object->quantity : null);
        $qty = is_object($qtyObj) && method_exists($qtyObj, 'value') ? $qtyObj->value() : (is_object($qtyObj) && property_exists($qtyObj, 'value') ? $qtyObj->value : (int)$qtyObj);

        $unitPrice = $this->firstValue($object, ['unitPrice', 'price', 'getUnitPrice']);
        $discount  = $this->firstValue($object, ['discount', 'getDiscount']);
        $taxRate   = $this->firstValue($object, ['taxRate', 'vat', 'getTaxRate']);
        $total     = $this->firstValue($object, ['totalAfterDiscount', 'total', 'getTotal']);

        return [
            self::KEY_ID        => (string) (method_exists($object, 'id') ? $object->id() : $object->id),
            self::KEY_ARTICLEID => (string) (method_exists($object, 'articleId') ? $object->articleId() : $object->articleId),
            self::KEY_QUANTITY  => $qty,
            self::KEY_UNITPRICE => $unitPrice ? $this->normalizer->normalize($unitPrice, $format, $context) : null,
            self::KEY_DISCOUNT  => $discount  ? $this->normalizer->normalize($discount,  $format, $context) : null,
            self::KEY_TAX       => $taxRate   ? $this->normalizer->normalize($taxRate,   $format, $context) : null,
            self::KEY_TOTAL     => $total     ? $this->normalizer->normalize($total,     $format, $context) : null,
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
        return [SaleLine::class => true];
    }
}
