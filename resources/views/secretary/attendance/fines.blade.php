@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Student Fines</h2>
            <a href="{{ route('secretary.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Back to Dashboard</a>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('secretary.attendance.fines') }}" class="mb-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Student Fines</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="text" 
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name, student ID, or activity..."
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('secretary.attendance.fines') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Fines Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Student
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Activity
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($fines as $fine)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $fine->student->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $fine->student->student_id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $fine->attendanceRecord->classworkActivity->title }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $fine->attendanceRecord->classworkActivity->due_date->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">₱{{ number_format($fine->amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $fine->is_paid 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $fine->is_paid ? 'Paid' : 'Unpaid' }}
                                </span>
                                @if($fine->is_paid)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Paid on {{ $fine->paid_at->format('M d, Y h:i A') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if(!$fine->is_paid)
                                    <form method="POST" action="{{ route('secretary.attendance.mark-fine-paid', $fine) }}" class="inline" data-no-csrf-handler>
                                        @csrf
                                        <button type="submit" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                            Mark as Paid
                                        </button>
                                    </form>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($fine->payment_reference)
                                            Ref: {{ $fine->payment_reference }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                No fines found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fines->hasPages())
            <div class="mt-4">
                {{ $fines->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 