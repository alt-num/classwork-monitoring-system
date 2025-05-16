<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\ClassworkActivity;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    public function create(ClassworkActivity $activity, Request $request)
    {
        $query = User::where('role', User::ROLE_STUDENT)
            ->where('section_id', auth()->user()->section_id);

        // Apply search if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('student_id', 'like', "%{$searchTerm}%");
            });
        }

        $students = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('secretary.attendance.create', compact('activity', 'students'));
    }

    public function store(Request $request, ClassworkActivity $activity)
    {
        $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:present,absent'],
        ]);

        DB::transaction(function () use ($request, $activity) {
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
                if ($status === 'absent') {
                    // Create or update fine for absent students
                    Fine::updateOrCreate(
                        [
                            'attendance_record_id' => $attendance->id,
                        ],
                        [
                            'student_id' => $studentId,
                            'amount' => 20, // Fine amount for absent
                            'is_paid' => false,
                        ]
                    );
                } else {
                    // If status changed to present, remove any existing fine
                    Fine::where('attendance_record_id', $attendance->id)->delete();
                }
            }
        });

        return redirect()->route('secretary.dashboard')
            ->with('success', 'Attendance has been updated successfully.');
    }

    public function report(Request $request)
    {
        $query = AttendanceRecord::query()
            ->whereHas('classworkActivity', function($q) {
                $q->currentYear();
            })
            ->whereHas('student', function($q) {
                $q->where('section_id', auth()->user()->section_id);
            })
            ->with(['student', 'classworkActivity', 'fine']);

        // Apply date filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply student filter
        if ($request->filled('student')) {
            $query->where('student_id', $request->student);
        }

        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('student_id', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('classworkActivity', function($sq) use ($search) {
                    $sq->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $records = $query->latest()->paginate(15)->withQueryString();
        
        $students = User::where('role', User::ROLE_STUDENT)
            ->where('section_id', auth()->user()->section_id)
            ->get();

        return view('secretary.attendance.report', compact('records', 'students'));
    }

    public function fines(Request $request)
    {
        $query = Fine::query()
            ->currentYear()
            ->whereHas('student', function($query) {
                $query->where('section_id', auth()->user()->section_id);
            })
            ->with(['student', 'attendanceRecord.classworkActivity']);

        // Apply search before pagination
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('student_id', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('attendanceRecord.classworkActivity', function($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%");
            });
        }

        $fines = $query->latest()->paginate(15)->withQueryString();

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