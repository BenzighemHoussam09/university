<?php

namespace App\Domain\Exam\Exceptions;

use RuntimeException;

class InvalidGradingTemplateException extends RuntimeException
{
    public static function sumNotTwenty(int $sum): self
    {
        return new self("Grading template components must sum to 20, got {$sum}.");
    }

    public static function componentOutOfRange(string $component, float $value, float $max): self
    {
        return new self("Component '{$component}' value {$value} is out of range [0, {$max}].");
    }
}
