@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Record Attendance</h2>
            <a href="{{ route('secretary.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Back to Dashboard</a>
        </div>

        <form method="POST" action="{{ route('secretary.attendance.store', $activity) }}" class="space-y-6">
            @csrf

            <!-- Activity Information -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold mb-2">Activity Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Title</p>
                        <p class="text-base font-medium">{{ $activity->title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Due Date</p>
                        <p class="text-base font-medium">{{ $activity->due_date->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Student</label>
                <input type="text" id="search" 
                    placeholder="Search by name or student ID..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
            </div>

            <!-- Students List -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Student ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600" id="studentsTableBody">
                        @forelse($students as $student)
                            <tr class="student-row" 
                                data-student-id="{{ $student->student_id }}"
                                data-student-name="{{ $student->name }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $student->student_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="attendance[{{ $student->id }}]" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                        <option value="present" {{ old("attendance.{$student->id}") == 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="late" {{ old("attendance.{$student->id}") == 'late' ? 'selected' : '' }}>Late</option>
                                        <option value="absent" {{ old("attendance.{$student->id}") == 'absent' ? 'selected' : '' }}>Absent</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No students found in your section
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Record Attendance
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const rows = document.querySelectorAll('.student-row');

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();

        rows.forEach(row => {
            const studentId = row.getAttribute('data-student-id').toLowerCase();
            const studentName = row.getAttribute('data-student-name').toLowerCase();
            const matches = studentId.includes(searchTerm) || studentName.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
        });
    });
});
</script>
@endpush

@endsection 