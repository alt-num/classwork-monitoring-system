<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', '!=', User::ROLE_ADMIN)
            ->with(['course', 'section']);

        // Apply search filter if search term is provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('student_id', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('role', 'like', "%{$searchTerm}%");
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

    /**
     * Format a name with proper capitalization
     */
    private function formatName(string $name): string
    {
        // Split the name into parts (assuming format: "lastname, firstname middlename")
        $parts = explode(',', $name);
        
        if (count($parts) !== 2) {
            // If not in "lastname, firstname" format, just capitalize each word
            return ucwords(strtolower(trim($name)));
        }

        $lastName = ucwords(strtolower(trim($parts[0])));
        $firstAndMiddle = ucwords(strtolower(trim($parts[1])));

        return $lastName . ', ' . $firstAndMiddle;
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
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

        // Format the name properly
        $validated['name'] = $this->formatName($validated['name']);

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

            // Clear the fines cache after creating a new user
            Cache::forget(Fine::getFinesCacheKey());

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
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
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

        // Format the name properly
        $validated['name'] = $this->formatName($validated['name']);

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

            // Clear the fines cache after updating user details
            Cache::forget(Fine::getFinesCacheKey());

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

        // Clear the fines cache after deleting a user
        Cache::forget(Fine::getFinesCacheKey());

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleRole(User $user)
    {
        if ($user->isAdmin()) {
            abort(403, 'Cannot modify admin role.');
        }

        try {
            // Check if making user a secretary
            if (!$user->isSecretary()) {
                // Check if a secretary already exists for this section
                $existingSecretary = User::where('role', User::ROLE_SECRETARY)
                    ->where('course_id', $user->course_id)
                    ->where('section_id', $user->section_id)
                    ->where('year', $user->year)
                    ->first();

                if ($existingSecretary) {
                    return redirect()->back()->with('error', 
                        'A secretary already exists for this section. Please remove the existing secretary first.');
                }
            }

            $oldRole = $user->role;
            $user->toggleSecretaryRole();
            $newRole = $user->role;

            $message = $newRole === User::ROLE_SECRETARY
                ? "User has been assigned as secretary. All section activities will be managed by this user."
                : "User has been removed from secretary role. All activities have been transferred to the new secretary (if one exists).";

            // Clear the fines cache after role change
            Cache::forget(Fine::getFinesCacheKey());

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                'Failed to change user role. Please try again or contact support if the problem persists.');
        }
    }

    public function resetCredentials(User $user)
    {
        $user->resetCredentials();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User credentials reset successfully.');
    }
}
