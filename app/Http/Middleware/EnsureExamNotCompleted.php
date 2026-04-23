<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureExamNotCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = Auth::guard('student')->user();
        $exam = $request->route('exam');

        if ($student && $exam && Schema::hasTable('exam_sessions')) {
            $examId = is_object($exam) ? $exam->getKey() : $exam;

            $status = DB::table('exam_sessions')
                ->where('exam_id', $examId)
                ->where('student_id', $student->id)
                ->value('status');

            if ($status === 'completed') {
                return redirect()->route('student.exams.results', ['exam' => $examId]);
            }
        }

        return $next($request);
    }
}
