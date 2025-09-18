<?php
// src/Infrastructure/Serialization/Normalizer/ProviderNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Partners\Provider;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes Provider while being tolerant with optional getters.
 * Only includes fields that actually exist.
 */
final class ProviderNormalizer implements NormalizerInterface
{
    public const KEY_ID       = 'id';
    public const KEY_NAME     = 'name';
    public const KEY_TAXID    = 'taxId';
    public const KEY_EMAIL    = 'email';
    public const KEY_PHONE    = 'phone';
    public const KEY_ADDRESS  = 'address';
    public const KEY_NOTES    = 'notes';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Provider;
    }

    /** @param Provider $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $out = [
            self::KEY_ID   => (string) $object->id(),
        ];

        $this->maybe($out, self::KEY_NAME, $object, ['name']);
        $this->maybe($out, self::KEY_TAXID, $object, ['taxId', 'vatNumber', 'nif']);
        $this->maybe($out, self::KEY_EMAIL, $object, ['email', 'billingEmail', 'contactEmail']);
        $this->maybe($out, self::KEY_PHONE, $object, ['phone', 'contactPhone']);
        $this->maybe($out, self::KEY_ADDRESS, $object, ['address', 'billingAddress']);
        $this->maybe($out, self::KEY_NOTES, $object, ['notes', 'remarks']);

        return $out;
    }

    /** @param array<string,mixed> $out */
    private function maybe(array &$out, string $key, object $target, array $methodsOrProps): void
    {
        foreach ($methodsOrProps as $name) {
            if (method_exists($target, $name)) {
                /** @var mixed $v */
                $v = $target->{$name}();
                if ($v !== null && $v !== '') {
                    $out[$key] = $v;
                }
                return;
            }
            if (property_exists($target, $name)) {
                /** @var mixed $v */
                $v = $target->{$name};
                if ($v !== null && $v !== '') {
                    $out[$key] = $v;
                }
                return;
            }
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Provider::class => true];
    }
}
