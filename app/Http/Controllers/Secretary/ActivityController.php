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
        $courses = Course::where('id', $user->course_id)->get();
        $sections = Section::where('id', $user->section_id)->get();
        return view('secretary.activities.create', compact('courses', 'sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
            'course_id' => ['required', 'exists:courses,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $validated['secretary_id'] = auth()->id();

        ClassworkActivity::create($validated);

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function edit(ClassworkActivity $activity)
    {
        $this->authorize('update', $activity);
        
        $courses = auth()->user()->courses;
        $sections = auth()->user()->sections;
        return view('secretary.activities.edit', compact('activity', 'courses', 'sections'));
    }

    public function update(Request $request, ClassworkActivity $activity)
    {
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
            'course_id' => ['required', 'exists:courses,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $activity->update($validated);

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(ClassworkActivity $activity)
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return redirect()->route('secretary.activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
} 