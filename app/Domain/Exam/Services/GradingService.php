<?php

namespace App\Domain\Exam\Services;

use App\Domain\Exam\Exceptions\InvalidGradingTemplateException;
use App\Models\GradeEntry;
use App\Models\GradingTemplate;

class GradingService
{
    /**
     * Normalize raw correct answers to the component maximum.
     */
    public function computeExamComponent(int $rawCorrect, int $total, int $examMax): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round($rawCorrect / $total * $examMax, 2);
    }

    /**
     * Compute the final grade as the sum of all four components.
     */
    public function computeFinalGrade(GradeEntry $entry): float
    {
        return round(
            $entry->exam_component + $entry->personal_work + $entry->attendance + $entry->participation,
            2
        );
    }

    /**
     * Validate a component value against the template maximum.
     *
     * @throws InvalidGradingTemplateException
     */
    public function validateComponentValue(string $component, float $value, GradingTemplate $template): void
    {
        $maxKey = $component.'_max';
        $max = $template->{$maxKey};

        if ($max === null) {
            throw new \InvalidArgumentException("Unknown component '{$component}'.");
        }

        if ($value < 0 || $value > $max) {
            throw InvalidGradingTemplateException::componentOutOfRange($component, $value, $max);
        }
    }
}
