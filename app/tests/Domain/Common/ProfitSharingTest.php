<?php
// tests/Domain/Common/ProfitSharingTest.php
declare(strict_types=1);

namespace Tests\Domain\Common;

use App\Domain\Common\{Percentage, ProfitSharing};
use Tests\Support\BaseTestCase;

final class ProfitSharingTest extends BaseTestCase
{
    public const MERCHANT_80 = 80.0;
    public const PROVIDER_20 = 20.0;

    public function test_can_be_instantiated(): void
    {
        $p = new ProfitSharing(new Percentage(self::MERCHANT_80), new Percentage(self::PROVIDER_20));
        self::assertInstanceOf(ProfitSharing::class, $p);
    }

    public function test_self_owned_factory_creates_instance(): void
    {
        $p = ProfitSharing::selfOwned();
        self::assertInstanceOf(ProfitSharing::class, $p);
    }
}
