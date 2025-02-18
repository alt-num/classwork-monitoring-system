<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\ClassworkActivity;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = ClassworkActivity::where('secretary_id', auth()->id())
            ->with(['course', 'section'])
            ->latest()
            ->paginate(10);

        return view('secretary.activities.index', compact('activities'));
    }

    public function create()
    {
        $user = auth()->user();
        return view('secretary.activities.create', [
            'course' => $user->course,
            'section' => $user->section,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
        ]);

        $user = auth()->user();
        $validated['secretary_id'] = $user->id;
        $validated['course_id'] = $user->course_id;
        $validated['section_id'] = $user->section_id;

        ClassworkActivity::create($validated);

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function edit(ClassworkActivity $activity)
    {
        // Check if the activity belongs to the current secretary
        if ($activity->secretary_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('secretary.activities.edit', [
            'activity' => $activity,
            'course' => auth()->user()->course,
            'section' => auth()->user()->section,
        ]);
    }

    public function update(Request $request, ClassworkActivity $activity)
    {
        // Check if the activity belongs to the current secretary
        if ($activity->secretary_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
        ]);

        $activity->update($validated);

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(ClassworkActivity $activity)
    {
        // Check if the activity belongs to the current secretary
        if ($activity->secretary_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $activity->delete();

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
} 