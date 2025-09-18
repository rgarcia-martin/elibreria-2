<?php
// src/Domain/Common/Discount.php
declare(strict_types=1);

namespace App\Domain\Common;

readonly class Discount
{

    private function __construct(
        public ?Money $fixed, 
        public ?Percentage $percent
    ) {}
    public static function none(): self { return new self(null,null); }
    public static function fixed(Money $m): self { return new self($m,null); }
    public static function percent(Percentage $p): self { return new self(null,$p); }

    public function apply(Money $base): Money
    {
        $r = $base;
        if ($this->percent) $r = $r->sub(new Money((int)round($base->amount*$this->percent->asRatio()), $base->currency));
        if ($this->fixed)   $r = $r->sub($this->fixed);
        return $r;
    }
}
