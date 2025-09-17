<?php
// tests/Infrastructure/Persistence/Doctrine/Mapper/GoodsReceiptMapperTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Money, Percentage, ProfitSharing, Quantity, Uuid as VUuid};
use App\Domain\Inventory\{
    GoodsReceipt, GoodsReceiptId, GoodsReceiptLine, GoodsReceiptPhoto, GoodsReceiptType
};
use App\Domain\Locations\LocationId;
use App\Domain\Partners\ProviderId;
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineGoodsReceipt, DoctrineGoodsReceiptLine, DoctrineGoodsReceiptPhoto};
use App\Infrastructure\Persistence\Doctrine\Mapper\GoodsReceiptMapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class GoodsReceiptMapperTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const MERCHANT_80 = 80.0;
    public const PROVIDER_20 = 20.0;
    public const MIME_JPEG = 'image/jpeg';
    public const PHOTO_URI = 'file:///tmp/photo.jpg';

    public function test_domain_to_entity_with_lines_and_photos(): void
    {
        $mapper = new GoodsReceiptMapper();

        $receipt = new GoodsReceipt(
            new GoodsReceiptId(Uuid::uuid4()->toString()),
            GoodsReceiptType::CONSIGNMENT,
            new \DateTimeImmutable('now'),
            new ProviderId(Uuid::uuid4()->toString()),
            (new \DateTimeImmutable('now'))->modify('+30 days')
        );

        $receipt->addLine(new GoodsReceiptLine(
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(2),
            new Money(500, self::CURRENCY_EUR),
            new ProfitSharing(new Percentage(self::MERCHANT_80), new Percentage(self::PROVIDER_20)),
            new LocationId(Uuid::uuid4()->toString())
        ));

        $receipt->addPhoto(new GoodsReceiptPhoto(
            new VUuid(Uuid::uuid4()->toString()),
            self::PHOTO_URI,
            self::MIME_JPEG
        ));

        $entity = $mapper->toEntity($receipt);

        self::assertInstanceOf(DoctrineGoodsReceipt::class, $entity);
        self::assertSame((string)$receipt->id(), $entity->id);
        self::assertSame(GoodsReceiptType::CONSIGNMENT->value, $entity->type);
        self::assertNotEmpty($entity->lines);
        self::assertCount(1, $entity->photos);

        $back = $mapper->toDomain($entity);
        self::assertSame((string)$receipt->id(), (string)$back->id());
        self::assertCount(1, $back->lines());
        self::assertCount(1, $back->photos());
    }
}
