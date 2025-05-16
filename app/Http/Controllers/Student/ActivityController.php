<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassworkActivity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user();
        
        // Base query for student's section activities
        $query = ClassworkActivity::whereHas('section', function($query) use ($student) {
            $query->where('id', $student->section_id);
        });

        // Apply year filter
        $year = $request->input('year', now()->year);
        $query->whereYear('due_date', $year);

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('attendanceRecords', function($query) use ($student, $status) {
                $query->where('student_id', $student->id)
                      ->where('status', $status);
            }, '>=', 0); // Use >= 0 to include activities without attendance records
        }

        // Get activities with relationships
        $activities = $query->with([
            'secretary', 
            'section.course', 
            'attendanceRecords' => function($query) use ($student) {
                $query->where('student_id', $student->id)->with('fine');
            }
        ])
        ->latest()
        ->paginate(10)
        ->withQueryString(); // Preserve filter parameters in pagination links

        return view('student.activities.index', compact('activities'));
    }
} 