@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h2 class="text-2xl font-bold mb-4">Student Dashboard</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Stats Cards -->
            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Enrolled Courses</h3>
                <p class="text-3xl font-bold text-blue-800 dark:text-blue-200">{{ $enrolledCourses ?? 0 }}</p>
            </div>
            
            <div class="bg-green-50 dark:bg-green-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-700 dark:text-green-300 mb-2">Completed Activities</h3>
                <p class="text-3xl font-bold text-green-800 dark:text-green-200">{{ $completedActivities ?? 0 }}</p>
            </div>
            
            <div class="bg-red-50 dark:bg-red-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-300 mb-2">Pending Activities</h3>
                <p class="text-3xl font-bold text-red-800 dark:text-red-200">{{ $pendingActivities ?? 0 }}</p>
            </div>
        </div>

        <!-- Current Course -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Your Course</h3>
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $currentCourse->name ?? 'Not Assigned' }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Section: {{ $currentSection->name ?? 'Not Assigned' }}</p>
                    </div>
                    <a href="{{ route('student.courses.show', $currentCourse ?? 0) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Upcoming Activities -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Upcoming Activities</h3>
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($upcomingActivities ?? [] as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                {{ $activity->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                {{ $activity->due_date->format('M d, Y') }}
                                <span class="text-sm text-gray-500 dark:text-gray-400">({{ $activity->due_date->diffForHumans() }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $activity->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                       ($activity->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                       'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                    {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('student.activities.show', $activity) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">View Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                No upcoming activities
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div>
            <h3 class="text-xl font-semibold mb-4">Recent Attendance</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($recentAttendance ?? [] as $attendance)
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $attendance->date->format('M d, Y') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $attendance->course->name }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $attendance->status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                               ($attendance->status === 'late' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                               'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>
                    @if($attendance->remarks)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $attendance->remarks }}</p>
                    @endif
                </div>
                @empty
                <div class="col-span-full text-center text-gray-500 dark:text-gray-400">
                    No recent attendance records
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection 