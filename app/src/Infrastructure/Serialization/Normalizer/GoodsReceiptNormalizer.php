<?php
// src/Infrastructure/Serialization/Normalizer/GoodsReceiptNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Inventory\GoodsReceipt;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class GoodsReceiptNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ID          = 'id';
    public const KEY_TYPE        = 'type';
    public const KEY_PROVIDER_ID = 'providerId';
    public const KEY_CREATED_AT  = 'createdAt';
    public const KEY_RETURN_DUE  = 'returnDueAt';
    public const KEY_LINES       = 'lines';
    public const KEY_PHOTOS      = 'photos';
    public const DATE_FORMAT     = \DateTimeInterface::ATOM;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GoodsReceipt;
    }

    /** @param GoodsReceipt $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $created   = $this->firstValue($object, ['createdAt', 'date', 'occurredAt', 'createdOn']);
        $returnDue = $this->firstValue($object, ['returnDueAt', 'dueAt', 'consignmentReturnDueAt']);
        $lines     = $this->firstValue($object, ['lines']);
        $photosArr = $this->firstValue($object, ['photos', 'pictures', 'images']);

        $providerId = method_exists($object, 'providerId') ? (string)$object->providerId() : (string)$object->providerId;

        return [
            self::KEY_ID          => (string) (method_exists($object, 'id') ? $object->id() : $object->id),
            self::KEY_TYPE        => method_exists($object, 'type') ? $object->type()->value : $object->type->value,
            self::KEY_PROVIDER_ID => $providerId,
            self::KEY_CREATED_AT  => $created instanceof \DateTimeInterface ? $created->format(self::DATE_FORMAT) : null,
            self::KEY_RETURN_DUE  => $returnDue instanceof \DateTimeInterface ? $returnDue->format(self::DATE_FORMAT) : null,
            self::KEY_LINES       => $this->normalizer->normalize($lines ?? [], $format, $context),
            self::KEY_PHOTOS      => is_array($photosArr)
                ? array_map(
                    static fn($p) => [
                        'id'   => (string) (method_exists($p, 'id') ? $p->id() : $p->id),
                        'uri'  => method_exists($p, 'uri') ? $p->uri() : $p->uri,
                        'mime' => method_exists($p, 'mime') ? $p->mime() : $p->mime,
                    ],
                    $photosArr
                )
                : [],
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
        return [GoodsReceipt::class => true];
    }
}
