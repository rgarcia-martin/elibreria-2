<?php
// tests/Domain/Common/BarcodeTest.php
declare(strict_types=1);

namespace Tests\Domain\Common;

use App\Domain\Common\Barcode;
use InvalidArgumentException;
use Tests\Support\BaseTestCase;

final class BarcodeTest extends BaseTestCase
{
    public const VALID = '0123456789012';
    public const EMPTY = '';

    public function test_it_keeps_value(): void
    {
        $b = new Barcode(self::VALID);
        self::assertSame(self::VALID, $b->value);
    }

    public function test_empty_barcode_is_not_allowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Barcode(self::EMPTY);
    }
}
