<?php
// src/Domain/Inventory/GoodsReceiptState.php
declare(strict_types=1);

namespace App\Domain\Inventory;

enum GoodsReceiptState: string { case OPEN='open'; case CLOSED='closed'; }
