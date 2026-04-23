<?php

namespace Tests\Unit\Domain;

use App\Domain\Exam\Services\GradingService;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    private GradingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradingService;
    }

    public function test_perfect_score_returns_exam_max(): void
    {
        $result = $this->service->computeExamComponent(6, 6, 12);
        $this->assertEqualsWithDelta(12.0, $result, 0.01);
    }

    public function test_zero_correct_returns_zero(): void
    {
        $result = $this->service->computeExamComponent(0, 6, 12);
        $this->assertEqualsWithDelta(0.0, $result, 0.01);
    }

    public function test_half_correct_returns_half_max(): void
    {
        $result = $this->service->computeExamComponent(3, 6, 12);
        $this->assertEqualsWithDelta(6.0, $result, 0.01);
    }

    public function test_result_is_rounded_to_two_decimals(): void
    {
        // 1/3 * 12 = 4.0, but let's use an awkward ratio
        // 1 of 3 * 10 = 3.333... → rounded to 3.33
        $result = $this->service->computeExamComponent(1, 3, 10);
        $this->assertEqualsWithDelta(3.33, $result, 0.001);
    }

    public function test_zero_total_returns_zero_without_division_error(): void
    {
        $result = $this->service->computeExamComponent(0, 0, 12);
        $this->assertEqualsWithDelta(0.0, $result, 0.01);
    }

    public function test_normalizes_correctly_with_different_exam_max(): void
    {
        // 4 of 5 correct, examMax = 20 → 4/5 * 20 = 16.0
        $result = $this->service->computeExamComponent(4, 5, 20);
        $this->assertEqualsWithDelta(16.0, $result, 0.01);
    }

    public function test_all_correct_with_max_4(): void
    {
        $result = $this->service->computeExamComponent(5, 5, 4);
        $this->assertEqualsWithDelta(4.0, $result, 0.01);
    }
}
