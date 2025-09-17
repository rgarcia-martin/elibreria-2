<?php
// src/Domain/Inventory/ConsignmentReturnStatus.php
declare(strict_types=1);

namespace App\Domain\Inventory;

enum ConsignmentReturnStatus: string { case DRAFT='draft'; case SENT='sent'; case CONFIRMED='confirmed'; }
