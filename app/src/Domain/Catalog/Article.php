<?php
// src/Domain/Catalog/Article.php
declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Common\{Money, Barcode, Percentage};
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class Article
{
    // ---- Public constraints & messages (avoid string literals) ----
    public const NAME_MIN_LENGTH = 1;
    public const NAME_MAX_LENGTH = 255;

    public const NORMALIZER_TRIM = 'trim';

    public const ERR_ID_REQUIRED       = 'Article id is required';
    public const ERR_NAME_REQUIRED     = 'Article name is required';
    public const ERR_NAME_MIN          = 'Article name is too short';
    public const ERR_NAME_MAX          = 'Article name is too long';
    public const ERR_PRICE_REQUIRED    = 'Article base price is required';
    public const ERR_PRICE_NEGATIVE    = 'Article base price must be greater than or equal to zero';
    public const ERR_TAX_REQUIRED      = 'Article tax rate is required';
    public const ERR_TAX_OUT_OF_RANGE  = 'Article tax rate must be between 0–1 (fraction) or 0–100 (percent)';

    /** Common method candidates to read numeric amounts from VOs (best-effort). */
    public const MONEY_NUMERIC_METHODS = ['getAmount', 'amount', 'toInt', 'toFloat', 'asFloat', 'value'];
    public const PERCENT_NUMERIC_METHODS = ['asFraction', 'toFloat', 'value'];

    public function __construct(
        #[Assert\NotNull(message: self::ERR_ID_REQUIRED)]
        #[Assert\Valid]
        private ArticleId $id,

        #[Assert\NotBlank(message: self::ERR_NAME_REQUIRED, normalizer: self::NORMALIZER_TRIM)]
        #[Assert\Length(
            min: self::NAME_MIN_LENGTH,
            max: self::NAME_MAX_LENGTH,
            minMessage: self::ERR_NAME_MIN,
            maxMessage: self::ERR_NAME_MAX,
            normalizer: self::NORMALIZER_TRIM
        )]
        private string $name,

        /** Nullable barcode; if present, cascade into its own constraints. */
        #[Assert\Valid]
        private ?Barcode $barcode,

        #[Assert\NotNull(message: self::ERR_PRICE_REQUIRED)]
        #[Assert\Valid]
        private Money $basePrice,

        #[Assert\NotNull(message: self::ERR_TAX_REQUIRED)]
        #[Assert\Valid]
        private Percentage $taxRate,
    ) {}

    // ---- Getters (read-only public API) ----
    public function id(): ArticleId { return $this->id; }
    public function name(): string { return $this->name; }
    public function barcode(): ?Barcode { return $this->barcode; }
    public function basePrice(): Money { return $this->basePrice; }
    public function taxRate(): Percentage { return $this->taxRate; }

    // ---- Callback for domain rules that involve nested VOs ----
    #[Assert\Callback]
    public function validateDomainRules(ExecutionContextInterface $context): void
    {
        // basePrice >= 0 (first try isNegative(); fallback to numeric readers)
        if (method_exists($this->basePrice, 'isNegative') && $this->basePrice->isNegative()) {
            $context->buildViolation(self::ERR_PRICE_NEGATIVE)->atPath('basePrice')->addViolation();
        } else {
            $amount = null;
            foreach (self::MONEY_NUMERIC_METHODS as $method) {
                if (method_exists($this->basePrice, $method)) {
                    $amount = $this->basePrice->{$method}();
                    break;
                }
            }
            if (is_numeric($amount) && (float) $amount < 0) {
                $context->buildViolation(self::ERR_PRICE_NEGATIVE)->atPath('basePrice')->addViolation();
            }
        }

        // taxRate in [0..1] (fraction) OR [0..100] (percent)
        $val = null;
        foreach (self::PERCENT_NUMERIC_METHODS as $method) {
            if (method_exists($this->taxRate, $method)) {
                $val = $this->taxRate->{$method}();
                break;
            }
        }
        if (is_numeric($val)) {
            $v = (float) $val;
            $valid = ($v >= 0.0 && $v <= 1.0) || ($v >= 0.0 && $v <= 100.0);
            if (!$valid) {
                $context->buildViolation(self::ERR_TAX_OUT_OF_RANGE)->atPath('taxRate')->addViolation();
            }
        }
    }
}
