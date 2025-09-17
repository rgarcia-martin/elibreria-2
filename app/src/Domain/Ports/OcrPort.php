<?php
// src/Domain/Ports/OcrPort.php
declare(strict_types=1);

namespace App\Domain\Ports;

interface OcrPort
{
    /** Retorna metadatos/valores OCR (si aplica) para enriquecer un albarán */
    public function analyzeReceiptPhoto(string $uri, string $mime): array;
}
