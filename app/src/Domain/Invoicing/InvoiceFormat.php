<?php
// src/Domain/Invoicing/InvoiceFormat.php
declare(strict_types=1);

namespace App\Domain\Invoicing;

enum InvoiceFormat: string { case PAPER='paper'; case DIGITAL='digital'; }
