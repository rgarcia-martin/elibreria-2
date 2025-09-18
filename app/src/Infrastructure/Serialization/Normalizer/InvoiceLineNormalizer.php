<?php
// src/Infrastructure/Serialization/Normalizer/InvoiceLineNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Invoicing\InvoiceLine;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class InvoiceLineNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_DESCRIPTION = 'description';
    public const KEY_QUANTITY    = 'quantity';
    public const KEY_UNIT_PRICE  = 'unitPrice';
    public const KEY_TAX_RATE    = 'taxRate';
    public const KEY_TOTAL       = 'total';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof InvoiceLine;
    }

    /** @param InvoiceLine $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $desc  = $this->firstValue($object, ['description', 'label', 'name']);
        $qty   = $this->firstValue($object, ['quantity', 'qty']);
        $unit  = $this->firstValue($object, ['unitPrice', 'price']);
        $tax   = $this->firstValue($object, ['taxRate', 'vat']);
        $total = $this->firstValue($object, ['total']);

        return [
            self::KEY_DESCRIPTION => $desc,
            self::KEY_QUANTITY    => (int)$qty,
            self::KEY_UNIT_PRICE  => $unit  ? $this->normalizer->normalize($unit,  $format, $context) : null,
            self::KEY_TAX_RATE    => $tax   ? $this->normalizer->normalize($tax,   $format, $context) : null,
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
        return [InvoiceLine::class => true];
    }
}
