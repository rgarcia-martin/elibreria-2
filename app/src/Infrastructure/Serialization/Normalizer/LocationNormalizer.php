<?php
// src/Infrastructure/Serialization/Normalizer/LocationNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Locations\Location;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes Location with graceful fallback for optional fields.
 */
final class LocationNormalizer implements NormalizerInterface
{
    public const KEY_ID   = 'id';
    public const KEY_CODE = 'code';
    public const KEY_NAME = 'name';
    public const KEY_DESC = 'description';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Location;
    }

    /** @param Location $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $out = [
            self::KEY_ID => (string) $object->id(),
        ];

        $this->maybe($out, self::KEY_CODE, $object, ['code']);
        $this->maybe($out, self::KEY_NAME, $object, ['name']);
        $this->maybe($out, self::KEY_DESC, $object, ['description']);

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
        return [Location::class => true];
    }
}
