<?php
// src/Domain/Inventory/GoodsReceiptPhoto.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Common\Uuid;

final class GoodsReceiptPhoto
{
    public function __construct(public Uuid $id, public string $uri, public string $mime) {}
}
