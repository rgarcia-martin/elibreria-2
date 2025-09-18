<?php
// src/Infrastructure/Serialization/Normalizer/CompanyIdentityNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Identity\CompanyIdentity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes CompanyIdentity for invoice emission contexts.
 */
final class CompanyIdentityNormalizer implements NormalizerInterface
{
    public const KEY_ID        = 'id';
    public const KEY_LEGALNAME = 'legalName';
    public const KEY_TAXID     = 'taxId';
    public const KEY_EMAIL     = 'email';
    public const KEY_ADDRESS   = 'address';
    public const KEY_PHONE     = 'phone';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CompanyIdentity;
    }

    /** @param CompanyIdentity $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            self::KEY_ID        => (string) $object->id(),
            self::KEY_LEGALNAME => (string) $object->legalName(),
            self::KEY_TAXID     => (string) $object->taxId(),
            self::KEY_EMAIL     => (string) $object->email(),
            self::KEY_ADDRESS   => (string) $object->address(),
            self::KEY_PHONE     => (string) $object->phone()
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [CompanyIdentity::class => true];
    }
}
