<?php
// tests/Infrastructure/Serialization/Normalizer/GoodsReceiptPhotoNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Inventory\GoodsReceiptPhoto;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class GoodsReceiptPhotoNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const URI  = 'file:///tmp/a.jpg';
    public const MIME = 'image/jpeg';

    public function test_normalizes_goods_receipt_photo(): void
    {
        $serializer = $this->makeSerializer();

        $photo = $this->createMock(GoodsReceiptPhoto::class);
        $photo->method('id')->willReturn(Uuid::uuid4()->toString());
        $photo->method('uri')->willReturn(self::URI);
        $photo->method('mime')->willReturn(self::MIME);

        $data = $serializer->normalize($photo);
        self::assertSame(self::URI, $data['uri']);
        self::assertSame(self::MIME, $data['mime']);
    }
}
