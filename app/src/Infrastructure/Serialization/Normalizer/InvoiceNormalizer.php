<?php
// src/Infrastructure/Serialization/Normalizer/InvoiceNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Invoicing\Invoice;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class InvoiceNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ID            = 'id';
    public const KEY_NUMBER        = 'number';
    public const KEY_DATE          = 'date';
    public const KEY_TYPE          = 'type';
    public const KEY_FORMAT        = 'format';
    public const KEY_COMPANY_ID    = 'companyIdentityId';
    public const KEY_SALE_ID       = 'saleId';
    public const KEY_RECTIFIES     = 'rectifiesInvoiceId';
    public const KEY_LINES         = 'lines';
    public const KEY_TOTAL         = 'total';
    public const DATE_FORMAT       = \DateTimeInterface::ATOM;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Invoice;
    }

    /** @param Invoice $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $date   = $this->firstValue($object, ['date', 'issuedAt', 'createdAt']);
        $lines  = $this->firstValue($object, ['lines', 'items']);
        $total  = $this->firstValue($object, ['total']);

        return [
            self::KEY_ID         => (string) (method_exists($object, 'id') ? $object->id() : $object->id),
            self::KEY_NUMBER     => method_exists($object, 'number') ? $object->number() : $object->number,
            self::KEY_DATE       => $date instanceof \DateTimeInterface ? $date->format(self::DATE_FORMAT) : null,
            self::KEY_TYPE       => method_exists($object, 'type') ? $object->type()->value : $object->type->value,
            self::KEY_FORMAT     => method_exists($object, 'format') ? $object->format()->value : $object->format->value,
            self::KEY_COMPANY_ID => (string) (method_exists($object, 'companyIdentityId') ? $object->companyIdentityId() : $object->companyIdentityId),
            self::KEY_SALE_ID    => (string) (method_exists($object, 'saleId') ? $object->saleId() : $object->saleId),
            self::KEY_RECTIFIES  => ($r = (method_exists($object, 'rectifiesInvoiceId') ? $object->rectifiesInvoiceId() : $object->rectifiesInvoiceId ?? null)) ? (string)$r : null,
            self::KEY_LINES      => $this->normalizer->normalize($lines ?? [], $format, $context),
            self::KEY_TOTAL      => $total ? $this->normalizer->normalize($total, $format, $context) : null,
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
        return [Invoice::class => true];
    }
}
