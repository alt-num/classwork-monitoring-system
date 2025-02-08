<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', User::ROLE_ADMIN)
            ->with(['course', 'section'])
            ->latest()
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $courses = Course::all();
        $sections = Section::with('course')->get();
        $years = [
            User::YEAR_FIRST => '1st Year',
            User::YEAR_SECOND => '2nd Year',
            User::YEAR_THIRD => '3rd Year',
            User::YEAR_FOURTH => '4th Year',
        ];
        
        return view('admin.users.create', compact('courses', 'sections', 'years'));
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_SECRETARY, User::ROLE_STUDENT])],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ];

        // Role-specific validation rules
        if ($request->input('role') === User::ROLE_STUDENT) {
            $rules = array_merge($rules, [
                'student_id' => ['required', 'string', 'unique:users,student_id'],
                'year' => [
                    'required',
                    'integer',
                    Rule::in([
                        User::YEAR_FIRST,
                        User::YEAR_SECOND,
                        User::YEAR_THIRD,
                        User::YEAR_FOURTH,
                    ]),
                ],
                'course_id' => [
                    'required',
                    'exists:courses,id',
                ],
                'section_id' => [
                    'required',
                    'string',
                    Rule::in(['A', 'B', 'C', 'D', 'E', 'F']),
                ],
            ]);
        } elseif ($request->input('role') === User::ROLE_SECRETARY) {
            $rules = array_merge($rules, [
                'student_id' => ['required', 'string', 'unique:users,student_id'],
                'course_id' => [
                    'required',
                    'exists:courses,id',
                ],
                'year' => [
                    'required',
                    'integer',
                    Rule::in([
                        User::YEAR_FIRST,
                        User::YEAR_SECOND,
                        User::YEAR_THIRD,
                        User::YEAR_FOURTH,
                    ]),
                ],
                'section_id' => [
                    'required',
                    'string',
                    Rule::in(['A', 'B', 'C', 'D', 'E', 'F']),
                ],
            ]);
        }

        $validated = $request->validate($rules);
        $validated['password'] = Hash::make($validated['password']);

        // Verify and get or create section
        try {
            // Check if section exists for this course and year
            $section = Section::where([
                'name' => $validated['section_id'],
                'year_level' => $validated['year'],
                'course_id' => $validated['course_id']
            ])->first();

            if (!$section) {
                // Create new section if it doesn't exist
                $section = Section::create([
                    'name' => $validated['section_id'],
                    'year_level' => $validated['year'],
                    'course_id' => $validated['course_id']
                ]);
            }

            // Replace section_id with actual section id
            $validated['section_id'] = $section->id;

            $user = User::create($validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create user. Please check if the course and section combination is valid.']);
        }
    }

    public function edit(User $user)
    {
        $courses = Course::all();
        $years = [
            User::YEAR_FIRST => '1st Year',
            User::YEAR_SECOND => '2nd Year',
            User::YEAR_THIRD => '3rd Year',
            User::YEAR_FOURTH => '4th Year',
        ];
        
        return view('admin.users.edit', compact('user', 'courses', 'years'));
    }

    public function update(Request $request, User $user)
    {
        // Common validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ];

        // Role-specific validation rules
        if ($user->role === User::ROLE_STUDENT) {
            $rules = array_merge($rules, [
                'student_id' => [
                    'required',
                    'string',
                    Rule::unique('users')->ignore($user->id),
                ],
                'year' => [
                    'required',
                    'integer',
                    Rule::in([
                        User::YEAR_FIRST,
                        User::YEAR_SECOND,
                        User::YEAR_THIRD,
                        User::YEAR_FOURTH,
                    ]),
                ],
                'course_id' => [
                    'required',
                    'exists:courses,id',
                ],
                'section_id' => [
                    'required',
                    'string',
                    Rule::in(['A', 'B', 'C', 'D', 'E', 'F']),
                ],
            ]);
        } elseif ($user->role === User::ROLE_SECRETARY) {
            $rules = array_merge($rules, [
                'student_id' => [
                    'required',
                    'string',
                    Rule::unique('users')->ignore($user->id),
                ],
                'course_id' => [
                    'required',
                    'exists:courses,id',
                ],
                'year' => [
                    'required',
                    'integer',
                    Rule::in([
                        User::YEAR_FIRST,
                        User::YEAR_SECOND,
                        User::YEAR_THIRD,
                        User::YEAR_FOURTH,
                    ]),
                ],
                'section_id' => [
                    'required',
                    'string',
                    Rule::in(['A', 'B', 'C', 'D', 'E', 'F']),
                ],
            ]);
        }

        $validated = $request->validate($rules);

        // Handle password update
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Verify and get or create section
        try {
            // Check if section exists for this course and year
            $section = Section::where([
                'name' => $validated['section_id'],
                'year_level' => $validated['year'],
                'course_id' => $validated['course_id']
            ])->first();

            if (!$section) {
                // Create new section if it doesn't exist
                $section = Section::create([
                    'name' => $validated['section_id'],
                    'year_level' => $validated['year'],
                    'course_id' => $validated['course_id']
                ]);
            }

            // Replace section_id with actual section id
            $validated['section_id'] = $section->id;

            $user->update($validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user. Please check if the course and section combination is valid.']);
        }
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
