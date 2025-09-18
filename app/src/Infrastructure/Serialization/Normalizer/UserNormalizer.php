<?php
// src/Infrastructure/Serialization/Normalizer/UserNormalizer.php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Normalizer;

use App\Domain\Users\User;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes User domain entity into a safe public shape.
 * NOTE: Do not include sensitive fields (password hashes, tokens).
 */
final class UserNormalizer implements NormalizerInterface
{
    public const KEY_ID       = 'id';
    public const KEY_USERNAME = 'username';
    public const KEY_EMAIL    = 'email';
    public const KEY_ROLES    = 'roles';
    public const KEY_ENABLED  = 'enabled';

    public const ROLE_METHOD  = 'roles';
    public const ROLE_VALUE   = 'value';
    public const ROLE_NAME    = 'name';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    /** @param User $object */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $out = [
            self::KEY_ID => (string) $object->id(),
        ];

        $this->maybe($out, self::KEY_USERNAME, $object, ['username', 'name']);
        $this->maybe($out, self::KEY_EMAIL,    $object, ['email']);
        $this->maybe($out, self::KEY_ENABLED,  $object, ['enabled', 'isEnabled']);

        // Roles array normalization tolerant to enums/VOs/strings
        $roles = [];
        if (method_exists($object, self::ROLE_METHOD)) {
            /** @var iterable<int,mixed> $raw */
            $raw = $object->{self::ROLE_METHOD}();
            foreach ($raw as $r) {
                $roles[] = $this->roleToString($r);
            }
        }
        if ($roles !== []) {
            $out[self::KEY_ROLES] = $roles;
        }

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

    private function roleToString(mixed $role): string
    {
        // Enum backed by string/int
        if ($role instanceof \BackedEnum) {
            return (string) $role->value;
        }
        // Pure enum
        if ($role instanceof \UnitEnum) {
            return $role->name;
        }
        // VO with "value" getter or property
        if (is_object($role)) {
            if (method_exists($role, self::ROLE_VALUE)) {
                /** @var mixed $v */
                $v = $role->{self::ROLE_VALUE}();
                return (string) $v;
            }
            if (property_exists($role, self::ROLE_VALUE)) {
                /** @var mixed $v */
                $v = $role->{self::ROLE_VALUE};
                return (string) $v;
            }
            if (method_exists($role, '__toString')) {
                return (string) $role;
            }
        }
        // String/int already
        return (string) $role;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [User::class => true];
    }
}
