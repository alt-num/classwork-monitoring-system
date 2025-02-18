@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Secretary Dashboard</h2>
            <a href="{{ route('secretary.profile.edit') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Edit Profile</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Stats Cards -->
            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Total Students</h3>
                <p class="text-3xl font-bold text-blue-800 dark:text-blue-200">{{ $totalStudents ?? 0 }}</p>
            </div>
            
            <div class="bg-green-50 dark:bg-green-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-700 dark:text-green-300 mb-2">Activities</h3>
                <p class="text-3xl font-bold text-green-800 dark:text-green-200">{{ $totalActivities ?? 0 }}</p>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-700 dark:text-yellow-300 mb-2">Pending Fines</h3>
                <p class="text-3xl font-bold text-yellow-800 dark:text-yellow-200">₱{{ number_format($pendingFines ?? 0, 2) }}</p>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-purple-700 dark:text-purple-300 mb-2">Collected Fines</h3>
                <p class="text-3xl font-bold text-purple-800 dark:text-purple-200">₱{{ number_format($collectedFines ?? 0, 2) }}</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('secretary.activities.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Add New Activity
                </a>
                <a href="{{ route('secretary.attendance.report') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Attendance Report
                </a>
                <a href="{{ route('secretary.attendance.fines') }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Manage Fines
                </a>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Recent Activities</h3>
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Attendance</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($recentActivities ?? [] as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $activity->title }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($activity->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $activity->due_date->format('M d, Y H:i') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $activity->due_date->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    Present: {{ $activity->attendanceRecords->where('status', 'present')->count() }}
                                </div>
                                <div class="text-sm text-red-600 dark:text-red-400">
                                    Absent: {{ $activity->attendanceRecords->where('status', 'absent')->count() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <a href="{{ route('secretary.attendance.create', $activity) }}" 
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                        Record Attendance
                                    </a>
                                    <a href="{{ route('secretary.activities.edit', $activity) }}" 
                                       class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                No recent activities
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Unpaid Fines -->
        <div>
            <h3 class="text-xl font-semibold mb-4">Recent Unpaid Fines</h3>
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($recentFines ?? [] as $fine)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $fine->student->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $fine->student->student_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $fine->attendanceRecord->classworkActivity->title }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $fine->attendanceRecord->classworkActivity->due_date->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-red-600 dark:text-red-400">₱{{ number_format($fine->amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="{{ route('secretary.attendance.mark-fine-paid', $fine) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                        class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300"
                                        onclick="return prompt('Enter payment reference:') && confirm('Are you sure you want to mark this fine as paid?')">
                                        Mark as Paid
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                No unpaid fines
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 