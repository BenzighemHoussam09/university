<?php

namespace App\Livewire\Teacher;

use App\Domain\Exam\Exceptions\InvalidGradingTemplateException;
use App\Models\GradingTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Settings extends Component
{
    public int $examMax = 12;

    public int $personalWorkMax = 4;

    public int $attendanceMax = 2;

    public int $participationMax = 2;

    public string $successMessage = '';

    public string $errorMessage = '';

    public function mount(): void
    {
        $teacher = Auth::guard('teacher')->user();
        $template = GradingTemplate::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->first();

        if ($template) {
            $this->examMax = $template->exam_max;
            $this->personalWorkMax = $template->personal_work_max;
            $this->attendanceMax = $template->attendance_max;
            $this->participationMax = $template->participation_max;
        }
    }

    #[Computed]
    public function componentSum(): int
    {
        return $this->examMax + $this->personalWorkMax + $this->attendanceMax + $this->participationMax;
    }

    public function save(): void
    {
        $this->validate([
            'examMax' => 'required|integer|min:0|max:20',
            'personalWorkMax' => 'required|integer|min:0|max:20',
            'attendanceMax' => 'required|integer|min:0|max:20',
            'participationMax' => 'required|integer|min:0|max:20',
        ]);

        $sum = $this->examMax + $this->personalWorkMax + $this->attendanceMax + $this->participationMax;

        if ($sum !== 20) {
            $this->errorMessage = "Components must sum to 20. Current sum: {$sum}.";
            $this->successMessage = '';

            return;
        }

        $teacher = Auth::guard('teacher')->user();

        try {
            $template = GradingTemplate::withoutGlobalScopes()
                ->where('teacher_id', $teacher->id)
                ->first();

            if ($template) {
                $template->exam_max = $this->examMax;
                $template->personal_work_max = $this->personalWorkMax;
                $template->attendance_max = $this->attendanceMax;
                $template->participation_max = $this->participationMax;
                $template->save();
            } else {
                GradingTemplate::withoutEvents(function () use ($teacher) {
                    GradingTemplate::create([
                        'teacher_id' => $teacher->id,
                        'exam_max' => $this->examMax,
                        'personal_work_max' => $this->personalWorkMax,
                        'attendance_max' => $this->attendanceMax,
                        'participation_max' => $this->participationMax,
                    ]);
                });
            }

            $this->successMessage = 'Settings saved successfully.';
            $this->errorMessage = '';
        } catch (InvalidGradingTemplateException $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = '';
        }
    }

    public function render(): View
    {
        return view('livewire.teacher.settings')
            ->layout('layouts.teacher');
    }
}
