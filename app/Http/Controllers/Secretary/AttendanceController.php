<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\ClassworkActivity;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function create(ClassworkActivity $activity)
    {
        // Get students from the secretary's section
        $students = User::where('role', User::ROLE_STUDENT)
            ->where('section_id', auth()->user()->section_id)
            ->get();

        return view('secretary.attendance.create', compact('activity', 'students'));
    }

    public function store(Request $request, ClassworkActivity $activity)
    {
        $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:present,late,absent'],
        ]);

        foreach ($request->attendance as $studentId => $status) {
            // Find existing record or create new one
            $attendance = AttendanceRecord::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'classwork_activity_id' => $activity->id,
                ],
                [
                    'status' => $status,
                    'recorded_by' => auth()->id(),
                ]
            );

            // Handle fines
            $fine = null;
            if ($status === 'late' || $status === 'absent') {
                $fine = 20; // Fine amount for both late and absent
            }

            if ($fine) {
                // Update or create fine
                Fine::updateOrCreate(
                    [
                        'attendance_record_id' => $attendance->id,
                    ],
                    [
                        'student_id' => $studentId,
                        'amount' => $fine,
                        'is_paid' => false,
                    ]
                );
            } else {
                // If status changed to present, remove any existing fine
                Fine::where('attendance_record_id', $attendance->id)->delete();
            }
        }

        return redirect()->route('secretary.dashboard')
            ->with('success', 'Attendance has been updated successfully.');
    }

    public function report(Request $request)
    {
        $query = AttendanceRecord::query()
            ->whereHas('student', function($q) {
                $q->where('section_id', auth()->user()->section_id);
            })
            ->with(['student', 'classworkActivity', 'fine']);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('student')) {
            $query->where('student_id', $request->student);
        }

        $records = $query->latest()->paginate(15);
        $students = User::where('role', User::ROLE_STUDENT)
            ->where('section_id', auth()->user()->section_id)
            ->get();

        return view('secretary.attendance.report', compact('records', 'students'));
    }

    public function fines()
    {
        $fines = Fine::whereHas('student', function($query) {
                $query->where('section_id', auth()->user()->section_id);
            })
            ->with(['student', 'attendanceRecord.classworkActivity'])
            ->latest()
            ->paginate(15);

        return view('secretary.attendance.fines', compact('fines'));
    }

    public function markFinePaid(Request $request, Fine $fine)
    {
        // Verify the fine belongs to a student in secretary's section
        if ($fine->student->section_id !== auth()->user()->section_id) {
            abort(403, 'Unauthorized action.');
        }

        $fine->markAsPaid();

        return redirect()->back()->with('success', 'Fine marked as paid successfully.');
    }
} 