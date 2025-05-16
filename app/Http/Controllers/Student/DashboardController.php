<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassworkActivity;
use App\Models\Fine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user();
        
        // Get activities for student's section
        $activities = ClassworkActivity::whereHas('section', function($query) use ($student) {
            $query->where('id', $student->section_id);
        })
        ->currentYear()
        ->with(['secretary', 'section.course', 'attendanceRecords' => function($query) use ($student) {
            $query->where('student_id', $student->id)->with('fine');
        }])
        ->latest()
        ->take(5)
        ->get();

        // Get attendance statistics
        $totalAttendance = $student->attendanceRecords()->count();
        $presentCount = $student->attendanceRecords()->where('status', 'present')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        // Calculate total fines
        $totalUnpaidFines = Fine::where('student_id', $student->id)
            ->where('is_paid', false)
            ->sum('amount');

        $totalPaidFines = Fine::where('student_id', $student->id)
            ->where('is_paid', true)
            ->sum('amount');

        return view('student.dashboard', compact(
            'student',
            'activities',
            'attendanceRate',
            'totalUnpaidFines',
            'totalPaidFines'
        ));
    }
} 