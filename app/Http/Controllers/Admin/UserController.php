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
    public function index(Request $request)
    {
        $query = User::where('role', '!=', User::ROLE_ADMIN)
            ->with(['course', 'section']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()
            ->paginate(10)
            ->withQueryString();

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
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
            'contact_number' => ['nullable', 'string', 'max:20'],
        ];

        $validated = $request->validate($rules);

        // Generate username and password from student ID
        $validated['username'] = User::generateUsername($validated['student_id']);
        $validated['password'] = Hash::make(User::generatePassword($validated['student_id']));
        $validated['role'] = User::ROLE_STUDENT; // Default role is student

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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'student_id' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
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
            'course_id' => ['required', 'exists:courses,id'],
            'section_id' => [
                'required',
                'string',
                Rule::in(['A', 'B', 'C', 'D', 'E', 'F']),
            ],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ];

        $validated = $request->validate($rules);

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

    public function toggleRole(User $user)
    {
        $user->toggleSecretaryRole();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User role updated successfully.');
    }

    public function resetCredentials(User $user)
    {
        $user->resetCredentials();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User credentials reset successfully.');
    }
}
