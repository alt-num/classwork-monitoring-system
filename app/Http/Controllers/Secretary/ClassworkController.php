<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\ClassworkActivity;
use App\Models\Section;
use Illuminate\Http\Request;

class ClassworkController extends Controller
{
    public function dashboard()
    {
        $activities = ClassworkActivity::where('created_by', auth()->id())
            ->with(['section', 'section.course'])
            ->latest()
            ->paginate(10);

        return view('secretary.dashboard', compact('activities'));
    }

    public function index()
    {
        $activities = ClassworkActivity::where('created_by', auth()->id())
            ->with(['section', 'section.course'])
            ->latest()
            ->paginate(10);

        return view('secretary.classwork.index', compact('activities'));
    }

    public function create()
    {
        // Only show sections that the secretary is assigned to
        $sections = Section::where('course_id', auth()->user()->course_id)
            ->where('id', auth()->user()->section_id)
            ->get();

        return view('secretary.classwork.create', compact('sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'section_id' => [
                'required',
                'exists:sections,id',
                function ($attribute, $value, $fail) {
                    $section = Section::find($value);
                    if ($section->course_id !== auth()->user()->course_id || 
                        $section->id !== auth()->user()->section_id) {
                        $fail('You can only create activities for your assigned section.');
                    }
                },
            ],
        ]);

        ClassworkActivity::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'section_id' => $request->section_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('secretary.classwork.index')
            ->with('success', 'Classwork activity created successfully.');
    }

    public function edit(ClassworkActivity $classwork)
    {
        $this->authorize('update', $classwork);

        $sections = Section::where('course_id', auth()->user()->course_id)
            ->where('id', auth()->user()->section_id)
            ->get();

        return view('secretary.classwork.edit', compact('classwork', 'sections'));
    }

    public function update(Request $request, ClassworkActivity $classwork)
    {
        $this->authorize('update', $classwork);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'section_id' => [
                'required',
                'exists:sections,id',
                function ($attribute, $value, $fail) {
                    $section = Section::find($value);
                    if ($section->course_id !== auth()->user()->course_id || 
                        $section->id !== auth()->user()->section_id) {
                        $fail('You can only create activities for your assigned section.');
                    }
                },
            ],
        ]);

        $classwork->update($request->all());

        return redirect()->route('secretary.classwork.index')
            ->with('success', 'Classwork activity updated successfully.');
    }

    public function destroy(ClassworkActivity $classwork)
    {
        $this->authorize('delete', $classwork);

        $classwork->delete();

        return redirect()->route('secretary.classwork.index')
            ->with('success', 'Classwork activity deleted successfully.');
    }
}
