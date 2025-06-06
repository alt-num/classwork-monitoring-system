@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Create New Activity</h2>
            <a href="{{ route('secretary.activities.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Back to Activities</a>
        </div>

        <form method="POST" action="{{ route('secretary.activities.store') }}" class="max-w-2xl" data-no-csrf-handler>
            @csrf

            <!-- Title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="4" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Due Date -->
            <div class="mb-4">
                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
                <input type="datetime-local" name="due_date" id="due_date" value="{{ old('due_date') }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                @error('due_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Course (Read-only) -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
                <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-600 rounded-md">
                    {{ auth()->user()->course->name }}
                </div>
                <input type="hidden" name="course_id" value="{{ auth()->user()->course_id }}">
            </div>

            <!-- Section (Read-only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-600 rounded-md">
                    Section {{ auth()->user()->section->name }}
                </div>
                <input type="hidden" name="section_id" value="{{ auth()->user()->section_id }}">
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create Activity
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 