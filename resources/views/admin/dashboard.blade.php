@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h1 class="text-2xl font-semibold mb-6">Admin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Students Card -->
            <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-blue-800 dark:text-blue-200">Total Students</h2>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-300">{{ $totalStudents }}</p>
            </div>

            <!-- Total Secretaries Card -->
            <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-green-800 dark:text-green-200">Total Secretaries</h2>
                <p class="text-3xl font-bold text-green-600 dark:text-green-300">{{ $totalSecretaries }}</p>
            </div>

            <!-- Total Courses Card -->
            <div class="bg-purple-100 dark:bg-purple-900 p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-purple-800 dark:text-purple-200">Total Courses</h2>
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-300">{{ $totalCourses }}</p>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('admin.users.index') }}" 
                   class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Manage Users</h3>
                    <p class="text-gray-600 dark:text-gray-400">Add, edit, or remove users from the system</p>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 