<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = User::where('role', User::ROLE_STUDENT)->count();
        $totalSecretaries = User::where('role', User::ROLE_SECRETARY)->count();
        $totalCourses = Course::count();

        return view('admin.dashboard', compact('totalStudents', 'totalSecretaries', 'totalCourses'));
    }
} 