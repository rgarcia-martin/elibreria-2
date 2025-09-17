<?php
// src/Domain/Inventory/ConsignmentBucket.php
declare(strict_types=1);

namespace App\Domain\Inventory;

enum ConsignmentBucket: string {
    case PURCHASED='purchased';
    case CONSIGNMENT_OK='consignment_ok';
    case CONSIGNMENT_OVERDUE='consignment_overdue';
    case CONSIGNMENT_RETURNED='consignment_returned';
}
