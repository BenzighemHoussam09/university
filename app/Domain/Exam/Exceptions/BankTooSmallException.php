<?php

namespace App\Domain\Exam\Exceptions;

use RuntimeException;

/**
 * Thrown when the question bank does not have enough questions
 * to satisfy the exam's per-difficulty distribution.
 */
class BankTooSmallException extends RuntimeException
{
    /**
     * Per-difficulty deficit: ['easy' => 2, 'medium' => 0, 'hard' => 1]
     *
     * @var array<string, int>
     */
    public array $deficits;

    /**
     * @param  array<string, int>  $deficits
     */
    public function __construct(array $deficits)
    {
        $this->deficits = $deficits;
        parent::__construct('The question bank does not have enough questions for this exam.');
    }
}
