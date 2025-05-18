@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Record Attendance</h2>
            <a href="{{ route('secretary.activities.index') }}" 
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Back to Activities
            </a>
        </div>

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

        <!-- Search and Pagination Controls -->
        <div class="mb-6 flex flex-col md:flex-row md:items-end md:space-x-4">
            <div class="flex-1 mb-2 md:mb-0">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Student</label>
                <input type="text" id="search" placeholder="Search by name or student ID..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="flex items-center space-x-2 mt-2 md:mt-0">
                <button id="prevPage" type="button" class="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Previous</button>
                <span id="pageInfo" class="text-sm"></span>
                <button id="nextPage" type="button" class="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Next</button>
            </div>
        </div>

        <!-- Attendance Form -->
        <form method="POST" action="{{ route('secretary.attendance.store', $activity) }}" class="space-y-6" data-no-csrf-handler id="attendanceForm">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600" id="studentsTable">
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
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600" id="studentsTbody">
                        @forelse($students as $student)
                            <tr data-name="{{ strtolower($student->name) }}" data-student-id="{{ strtolower($student->student_id) }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $student->student_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="attendance[{{ $student->id }}]" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                        @php
                                            $existingRecord = $activity->attendanceRecords->where('student_id', $student->id)->first();
                                            $currentStatus = $existingRecord ? $existingRecord->status : 'present';
                                        @endphp
                                        <option value="present" {{ $currentStatus === 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ $currentStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No students found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->count() > 0)
                <div class="flex justify-end mt-6">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Record Attendance
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Client-side pagination and search
    document.addEventListener('DOMContentLoaded', function() {
        const rows = Array.from(document.querySelectorAll('#studentsTbody tr[data-name]'));
        const searchInput = document.getElementById('search');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');
        const pageSize = 10;
        let currentPage = 1;
        let filteredRows = rows;

        function filterRows() {
            const term = searchInput.value.toLowerCase().trim();
            filteredRows = rows.filter(row => {
                const name = row.getAttribute('data-name');
                const studentId = row.getAttribute('data-student-id');
                return name.includes(term) || studentId.includes(term);
            });
            currentPage = 1;
            renderPage();
        }

        function renderPage() {
            rows.forEach(row => row.style.display = 'none');
            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;
            filteredRows.slice(start, end).forEach(row => row.style.display = '');
            pageInfo.textContent = `Page ${filteredRows.length === 0 ? 0 : currentPage} of ${Math.max(1, Math.ceil(filteredRows.length / pageSize))}`;
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = end >= filteredRows.length;
        }

        searchInput.addEventListener('input', filterRows);
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderPage();
            }
        });
        nextBtn.addEventListener('click', function() {
            if ((currentPage * pageSize) < filteredRows.length) {
                currentPage++;
                renderPage();
            }
        });

        // On submit, ensure all attendance values are included
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            // All select elements are already in the DOM, so all values will be submitted
            // even if their rows are hidden by pagination.
        });

        // Initial render
        filterRows();
    });
</script>
@endpush
@endsection 