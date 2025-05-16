<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\ClassworkActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = ClassworkActivity::where('secretary_id', auth()->id())
            ->currentYear()
            ->with(['section.course', 'attendanceRecords'])
            ->latest()
            ->paginate(10);

        return view('secretary.activities.index', compact('activities'));
    }

    public function create()
    {
        return view('secretary.activities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date'],
        ]);

        $user = auth()->user();

        // Start a database transaction
        DB::beginTransaction();

        try {
            $activity = ClassworkActivity::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'],
                'secretary_id' => $user->id,
                'section_id' => $user->section_id,
                'course_id' => $user->course_id,
                'year' => $user->year,
            ]);

            // Auto create an attendance record for the secretary as organizer
            $activity->attendanceRecords()->create([
                'student_id' => $user->id,
                'status' => 'organizer',
                'recorded_by' => $user->id,
                'remarks' => 'Activity organizer'
            ]);

            DB::commit();

            return redirect()->route('secretary.activities.index')
                ->with('success', 'Activity created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(ClassworkActivity $activity)
    {
        if ($activity->secretary_id !== auth()->id()) {
            abort(403);
        }

        return view('secretary.activities.edit', compact('activity'));
    }

    public function update(Request $request, ClassworkActivity $activity)
    {
        if ($activity->secretary_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date'],
        ]);

        $activity->update($validated);

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(ClassworkActivity $activity)
    {
        if ($activity->secretary_id !== auth()->id()) {
            abort(403);
        }

        $activity->delete();

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
} 