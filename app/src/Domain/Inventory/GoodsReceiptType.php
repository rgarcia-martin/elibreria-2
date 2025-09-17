<?php
// src/Domain/Inventory/GoodsReceiptType.php
declare(strict_types=1);

namespace App\Domain\Inventory;

enum GoodsReceiptType: string { case PURCHASED='purchased'; case CONSIGNMENT='consignment'; }
