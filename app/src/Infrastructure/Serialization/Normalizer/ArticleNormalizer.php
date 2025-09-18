<?php
// src/Infrastructure/Serialization/Normalizer/ArticleNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Catalog\Article;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes Article aggregate (or entity) from Catalog BC.
 */
final class ArticleNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const KEY_ID        = 'id';
    public const KEY_NAME      = 'name';
    public const KEY_BARCODE   = 'barcode';
    public const KEY_BASEPRICE = 'basePrice';
    public const KEY_TAXRATE   = 'taxRate';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Article;
    }

    /** @param Article $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        // Assuming getters exist in the domain model:
        return [
            self::KEY_ID        => (string) $object->id(),
            self::KEY_NAME      => $object->name(),
            self::KEY_BARCODE   => $this->normalizer->normalize($object->barcode(), $format, $context),
            self::KEY_BASEPRICE => $this->normalizer->normalize($object->basePrice(), $format, $context),
            self::KEY_TAXRATE   => $this->normalizer->normalize($object->taxRate(), $format, $context),
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Article::class => true];
    }
}
