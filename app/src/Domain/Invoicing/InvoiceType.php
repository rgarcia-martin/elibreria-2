<?php
// src/Domain/Invoicing/InvoiceType.php
declare(strict_types=1);

namespace App\Domain\Invoicing;

enum InvoiceType: string { case SIMPLIFIED='simplified'; case NORMAL='normal'; case RECTIFICATION='rectification'; }
