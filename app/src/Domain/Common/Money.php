<?php
// src/Domain/Common/Money.php
declare(strict_types=1);

namespace App\Domain\Common;

readonly class Money
{
    public function __construct(public int $amount, public string $currency='EUR')
    {
        if ($amount < 0) throw new \InvalidArgumentException('Importe negativo');
        if (strlen($currency) !== 3) throw new \InvalidArgumentException('Moneda ISO-4217 inválida');
    }
    public static function zero(string $currency='EUR'): self { return new self(0,$currency); }
    public function add(self $o): self { $this->same($o); return new self($this->amount + $o->amount,$this->currency); }
    public function sub(self $o): self { $this->same($o); return new self(max(0,$this->amount - $o->amount),$this->currency); }
    public function mul(float $k): self { return new self((int)round($this->amount*$k),$this->currency); }
    private function same(self $o): void { if ($this->currency !== $o->currency) throw new \LogicException('Monedas distintas'); }
}
