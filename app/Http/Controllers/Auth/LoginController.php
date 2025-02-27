<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // No middleware here - we'll handle it in routes
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Get summary of fines by course and section
        $finesByCourse = \App\Models\Fine::join('users', 'fines.student_id', '=', 'users.id')
            ->join('courses', 'users.course_id', '=', 'courses.id')
            ->selectRaw('courses.name as course_name, 
                         SUM(CASE WHEN fines.is_paid = 1 THEN fines.amount ELSE 0 END) as collected, 
                         SUM(CASE WHEN fines.is_paid = 0 THEN fines.amount ELSE 0 END) as pending,
                         COUNT(*) as total_fines')
            ->groupBy('courses.id', 'courses.name')
            ->get();

        $finesBySection = \App\Models\Fine::join('users', 'fines.student_id', '=', 'users.id')
            ->join('sections', 'users.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->selectRaw('courses.name as course_name, 
                         sections.name as section_name,
                         SUM(CASE WHEN fines.is_paid = 1 THEN fines.amount ELSE 0 END) as collected, 
                         SUM(CASE WHEN fines.is_paid = 0 THEN fines.amount ELSE 0 END) as pending,
                         COUNT(*) as total_fines')
            ->groupBy('courses.id', 'courses.name', 'sections.id', 'sections.name')
            ->get();

        // Get totals
        $totalStats = \App\Models\Fine::selectRaw('
            SUM(amount) as total_amount,
            SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as total_collected,
            SUM(CASE WHEN is_paid = 0 THEN amount ELSE 0 END) as total_pending,
            COUNT(*) as total_fines,
            COUNT(CASE WHEN is_paid = 1 THEN 1 END) as paid_fines,
            COUNT(CASE WHEN is_paid = 0 THEN 1 END) as unpaid_fines
        ')->first();

        return view('auth.login', compact('finesByCourse', 'finesBySection', 'totalStats'));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
