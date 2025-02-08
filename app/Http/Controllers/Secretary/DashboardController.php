<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassworkActivity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $secretary = auth()->user();
        
        // Get students from secretary's assigned section
        $students = User::where('role', User::ROLE_STUDENT)
            ->where('course_id', $secretary->course_id)
            ->where('section_id', $secretary->section_id)
            ->with(['course', 'section'])
            ->get();

        // Get activities created by this secretary
        $recentActivities = ClassworkActivity::where('secretary_id', $secretary->id)
            ->with(['section.course'])
            ->latest()
            ->take(5)
            ->get();

        // Get upcoming deadlines
        $upcomingDeadlines = ClassworkActivity::where('secretary_id', $secretary->id)
            ->where('due_date', '>', now())
            ->with(['section.course'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Calculate statistics
        $totalStudents = $students->count();
        $totalActivities = ClassworkActivity::where('secretary_id', $secretary->id)->count();
        $pendingActivities = ClassworkActivity::where('secretary_id', $secretary->id)
            ->where('due_date', '>', now())
            ->count();

        return view('secretary.dashboard', compact(
            'students',
            'recentActivities',
            'upcomingDeadlines',
            'totalStudents',
            'totalActivities',
            'pendingActivities'
        ));
    }
} 