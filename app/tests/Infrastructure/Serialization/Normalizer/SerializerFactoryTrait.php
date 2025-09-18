<?php
// tests/Infrastructure/Serialization/Normalizer/SerializerFactoryTrait.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ReflectionClass;

/**
 * Builds a Serializer that automatically discovers and registers
 * all normalizers under the namespace:
 *   App\Infrastructure\Serialization\Normalizer
 *
 * This avoids touching tests when new normalizers are added.
 */
trait SerializerFactoryTrait
{
    /** Test constants commonly reused across tests */
    public const CURRENCY_EUR = 'EUR';
    public const TAX_21       = 21.0;

    /** Filesystem / Namespace resolution constants */
    private const NS_PREFIX                = 'App\\Infrastructure\\Serialization\\Normalizer\\';
    private const SRC_DIR_SEG_1           = 'src';
    private const SRC_DIR_SEG_2           = 'Infrastructure';
    private const SRC_DIR_SEG_3           = 'Serialization';
    private const SRC_DIR_SEG_4           = 'Normalizer';
    private const FILE_EXT_PHP            = '.php';
    private const GLOB_PATTERN_PHP        = '*.php';

    /** Directory ascents from this file to project root (tests/Infrastructure/Serialization/Normalizer/ -> root) */
    private const DIR_UP_TO_ROOT_FROM_HERE = 4;

    /**
     * Factory: returns a Serializer with all domain normalizers discovered,
     * plus the ObjectNormalizer as a fallback (always last).
     */
    protected function makeSerializer(): Serializer
    {
        $normalizers = $this->discoverNormalizers();
        $normalizers[] = new ObjectNormalizer(); // fallback, always last

        return new Serializer($normalizers);
    }

    /**
     * Discovers and instantiates every class in
     * src/Infrastructure/Serialization/Normalizer that:
     *  - is instantiable
     *  - implements NormalizerInterface
     */
    private function discoverNormalizers(): array
    {
        $dir = $this->normalizerDirectory();
        if (!is_dir($dir)) {
            return []; // graceful fallback if path is missing
        }

        /** @var array<int,string> $files */
        $files = glob($dir . DIRECTORY_SEPARATOR . self::GLOB_PATTERN_PHP) ?: [];
        $classes = [];

        foreach ($files as $file) {
            $fqcn = $this->fqcnFromFile($file);
            if ($fqcn === null) {
                continue;
            }
            if (!class_exists($fqcn)) {
                // Ensure Composer can autoload it (class_exists() triggers autoload)
                continue;
            }
            if (!$this->isInstantiableNormalizer($fqcn)) {
                continue;
            }
            $classes[] = $fqcn;
        }

        // Deterministic order (useful for predictable snapshots/comparisons)
        sort($classes, SORT_STRING);

        // Instantiate
        $instances = [];
        foreach ($classes as $class) {
            /** @var class-string<NormalizerInterface> $class */
            $instances[] = new $class();
        }

        return $instances;
    }

    /**
     * Resolves the filesystem directory where normalizers live.
     */
    private function normalizerDirectory(): string
    {
        $root = dirname(__DIR__, self::DIR_UP_TO_ROOT_FROM_HERE);

        return $root
            . DIRECTORY_SEPARATOR . self::SRC_DIR_SEG_1
            . DIRECTORY_SEPARATOR . self::SRC_DIR_SEG_2
            . DIRECTORY_SEPARATOR . self::SRC_DIR_SEG_3
            . DIRECTORY_SEPARATOR . self::SRC_DIR_SEG_4;
    }

    /**
     * Maps a PHP file path to its expected FQCN within the target namespace.
     */
    private function fqcnFromFile(string $file): ?string
    {
        $basename = basename($file);
        if (!str_ends_with($basename, self::FILE_EXT_PHP)) {
            return null;
        }

        $className = substr($basename, 0, -\strlen(self::FILE_EXT_PHP));
        if ($className === '' || $className === false) {
            return null;
        }

        return self::NS_PREFIX . $className;
    }

    /**
     * Verifies the class is a concrete NormalizerInterface implementation.
     *
     * @param class-string $fqcn
     */
    private function isInstantiableNormalizer(string $fqcn): bool
    {
        $ref = new ReflectionClass($fqcn);

        if (!$ref->isInstantiable()) {
            return false;
        }

        if (!is_a($fqcn, NormalizerInterface::class, true)) {
            return false;
        }

        return true;
    }
}
