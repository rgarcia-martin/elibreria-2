<?php
// tests/Infrastructure/Serialization/Normalizer/ArticleNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Catalog\{Article, ArticleId};
use App\Domain\Common\{Barcode, Money, Percentage};
use App\Infrastructure\Serialization\Normalizer\ArticleNormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ArticleNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const NAME = 'Article';
    public const BARCODE = '0123456789012';
    public const PRICE = 1999;

    public function test_normalizes_article(): void
    {
        $serializer = $this->makeSerializer();
        $a = new Article(
            new ArticleId(Uuid::uuid4()->toString()),
            self::NAME,
            new Barcode(self::BARCODE),
            new Money(self::PRICE, self::CURRENCY_EUR),
            new Percentage(self::TAX_21)
        );

        $data = $serializer->normalize($a);
        self::assertSame(self::NAME, $data[ArticleNormalizer::KEY_NAME]);
        self::assertSame(self::BARCODE, $data[ArticleNormalizer::KEY_BARCODE]);
        self::assertSame(self::PRICE, $data[ArticleNormalizer::KEY_BASEPRICE]['amount']);
    }
}
