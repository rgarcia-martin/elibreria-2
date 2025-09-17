<?php
// tests/Infrastructure/Persistence/Doctrine/Mapper/ArticleMapperTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Catalog\{Article, ArticleId};
use App\Domain\Common\{Barcode, Money, Percentage};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineArticle;
use App\Infrastructure\Persistence\Doctrine\Mapper\ArticleMapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ArticleMapperTest extends TestCase
{
    public const NAME = 'Test Article';
    public const BARCODE = '0123456789012';
    public const CURRENCY_EUR = 'EUR';
    public const TAX_21 = 21.0;

    public function test_domain_to_entity_and_back(): void
    {
        $mapper = new ArticleMapper();

        $domain = new Article(
            new ArticleId(Uuid::uuid4()->toString()),
            self::NAME,
            new Barcode(self::BARCODE),
            new Money(999, self::CURRENCY_EUR),
            new Percentage(self::TAX_21)
        );

        $entity = $mapper->toEntity($domain);

        self::assertInstanceOf(DoctrineArticle::class, $entity);
        self::assertSame((string)$domain->id(), $entity->id);
        self::assertSame(self::NAME, $entity->name);
        self::assertSame(self::BARCODE, $entity->barcode);

        $back = $mapper->toDomain($entity);

        self::assertSame((string)$domain->id(), (string)$back->id());
        self::assertSame(self::NAME, $back->name());
        self::assertSame(self::BARCODE, $back->barcode()?->value);
        self::assertSame(999, $back->basePrice()->amount);
        self::assertSame(self::CURRENCY_EUR, $back->basePrice()->currency);
        self::assertSame(self::TAX_21, $back->taxRate()->value);
    }
}
