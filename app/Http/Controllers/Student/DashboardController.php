<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassworkActivity;
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
        ->with(['secretary', 'section.course'])
        ->latest()
        ->paginate(10);

        // Get attendance statistics
        $totalAttendance = $student->attendanceRecords()->count();
        $presentCount = $student->attendanceRecords()->where('status', 'present')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        return view('student.dashboard', compact('student', 'activities', 'attendanceRate'));
    }
} 