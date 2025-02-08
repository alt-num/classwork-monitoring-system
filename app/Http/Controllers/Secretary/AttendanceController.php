<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function create()
    {
        $secretary = auth()->user();
        
        // Get students from secretary's assigned section
        $students = User::where('role', User::ROLE_STUDENT)
            ->where('course_id', $secretary->course_id)
            ->where('section_id', $secretary->section_id)
            ->with(['course', 'section'])
            ->get();

        return view('secretary.attendance.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'students' => ['required', 'array'],
            'students.*' => ['required', 'exists:users,id'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:present,absent,late'],
        ]);

        foreach ($validated['students'] as $index => $studentId) {
            Attendance::create([
                'user_id' => $studentId,
                'secretary_id' => auth()->id(),
                'date' => $validated['date'],
                'status' => $validated['status'][$index],
            ]);
        }

        return redirect()->route('secretary.dashboard')
            ->with('success', 'Attendance recorded successfully.');
    }
} 