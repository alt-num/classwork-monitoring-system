<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassworkActivity;
use App\Models\Fine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $secretary = auth()->user();
        
        // Get students and secretary from the assigned section
        $students = User::whereIn('role', [User::ROLE_STUDENT, User::ROLE_SECRETARY])
            ->where('section_id', $secretary->section_id)
            ->with(['course', 'section'])
            ->get();

        // Get activities created by this secretary for current year
        $recentActivities = ClassworkActivity::where('secretary_id', $secretary->id)
            ->currentYear()
            ->with(['attendanceRecords', 'section.course'])
            ->latest()
            ->take(5)
            ->get();

        // Calculate statistics for current year
        $totalStudents = $students->count();
        $totalActivities = ClassworkActivity::where('secretary_id', $secretary->id)
            ->currentYear()
            ->count();

        // Calculate fines for the section in current year
        $pendingFines = Fine::whereHas('student', function($query) use ($secretary) {
                $query->where('section_id', $secretary->section_id);
            })
            ->currentYear()
            ->where('is_paid', false)
            ->sum('amount');

        $collectedFines = Fine::whereHas('student', function($query) use ($secretary) {
                $query->where('section_id', $secretary->section_id);
            })
            ->currentYear()
            ->where('is_paid', true)
            ->sum('amount');

        // Get recent unpaid fines for current year
        $recentFines = Fine::whereHas('student', function($query) use ($secretary) {
                $query->where('section_id', $secretary->section_id);
            })
            ->currentYear()
            ->where('is_paid', false)
            ->with(['student', 'attendanceRecord.classworkActivity'])
            ->latest()
            ->take(5)
            ->get();

        // Calculate section's attendance statistics
        $totalAttendanceRecords = 0;
        $presentRecords = 0;
        foreach ($students as $student) {
            $studentRecords = $student->attendanceRecords()->count();
            $studentPresent = $student->attendanceRecords()->where('status', 'present')->count();
            $totalAttendanceRecords += $studentRecords;
            $presentRecords += $studentPresent;
        }
        $sectionAttendanceRate = $totalAttendanceRecords > 0 
            ? round(($presentRecords / $totalAttendanceRecords) * 100) 
            : 0;

        return view('secretary.dashboard', compact(
            'students',
            'recentActivities',
            'totalStudents',
            'totalActivities',
            'pendingFines',
            'collectedFines',
            'recentFines',
            'sectionAttendanceRate'
        ));
    }
} 