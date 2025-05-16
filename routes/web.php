<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Secretary\ActivityController;
use App\Http\Controllers\Secretary\DashboardController as SecretaryDashboardController;
use App\Http\Controllers\Secretary\ProfileController as SecretaryProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SecretaryMiddleware;
use App\Http\Middleware\StudentMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Secretary\AttendanceController;
use App\Models\Course;
use App\Models\Fine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Public routes
Route::get('/', function () {
    // Cache the fines data for 1 hour with year-based key
    $coursesFines = Cache::remember(Fine::getFinesCacheKey(), 3600, function () {
        $currentYear = now()->year;
        return Course::select([
                'courses.name as course_name',
                'courses.code as course_code',
                DB::raw('COUNT(CASE WHEN users.role IN ("student", "secretary") THEN users.id END) as total_students'),
                DB::raw("COALESCE(SUM(CASE WHEN fines.is_paid = 0 AND strftime('%Y', fines.created_at) = '{$currentYear}' THEN fines.amount ELSE 0 END), 0) as total_unpaid"),
                DB::raw("COALESCE(SUM(CASE WHEN fines.is_paid = 1 AND strftime('%Y', fines.created_at) = '{$currentYear}' THEN fines.amount ELSE 0 END), 0) as total_paid"),
                DB::raw("COALESCE(SUM(CASE WHEN strftime('%Y', fines.created_at) = '{$currentYear}' THEN fines.amount ELSE 0 END), 0) as total_amount")
            ])
            ->leftJoin('users', 'courses.id', '=', 'users.course_id')
            ->leftJoin('fines', 'users.id', '=', 'fines.student_id')
            ->groupBy('courses.id', 'courses.name', 'courses.code')
            ->orderBy('courses.name')
            ->get();
    });

    return view('welcome', compact('coursesFines'));
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Default dashboard route - redirects based on user role
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'secretary' => redirect()->route('secretary.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    })->name('dashboard');

    // Admin routes
    Route::middleware(AdminMiddleware::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-role', [UserController::class, 'toggleRole'])->name('users.toggle-role');
        Route::post('/users/{user}/reset-credentials', [UserController::class, 'resetCredentials'])->name('users.reset-credentials');
    });

    // Secretary routes
    Route::middleware(SecretaryMiddleware::class)->prefix('secretary')->name('secretary.')->group(function () {
        Route::get('/dashboard', [SecretaryDashboardController::class, 'index'])->name('dashboard');
        Route::resource('activities', ActivityController::class);
        
        // Attendance routes
        Route::get('/attendance/create/{activity}', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance/{activity}/store', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('/attendance/fines', [AttendanceController::class, 'fines'])->name('attendance.fines');
        Route::post('/attendance/fines/{fine}/mark-paid', [AttendanceController::class, 'markFinePaid'])->name('attendance.mark-fine-paid');

        // Profile routes
        Route::get('/profile', [SecretaryProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SecretaryProfileController::class, 'update'])->name('profile.update');
    });

    // Student routes
    Route::middleware(StudentMiddleware::class)->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/activities', [App\Http\Controllers\Student\ActivityController::class, 'index'])->name('activities.index');
        Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    });
});
