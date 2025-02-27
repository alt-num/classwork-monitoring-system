@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-gray-100 mb-6">
            Login to CMS
        </h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Username -->
            <div>
                <label for="username" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    Username
                </label>

                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">

                @error('username')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    Password
                </label>

                <input id="password" type="password" name="password" required
                    class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 [color-scheme:dark]">

                @error('password')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Log in
                </button>
            </div>
        </form>
    </div>

    <!-- Fines Summary Section -->
    <div class="w-full sm:max-w-3xl mt-8 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        <h2 class="text-xl font-bold text-center text-gray-900 dark:text-gray-100 mb-4">
            Fines Summary
        </h2>

        <!-- Overall Stats -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Fines</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($totalStats->total_amount ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalStats->total_fines ?? 0 }} records</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Collected</p>
                    <p class="text-lg font-semibold text-green-600 dark:text-green-400">₱{{ number_format($totalStats->total_collected ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalStats->paid_fines ?? 0 }} records</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="text-lg font-semibold text-red-600 dark:text-red-400">₱{{ number_format($totalStats->total_pending ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalStats->unpaid_fines ?? 0 }} records</p>
                </div>
            </div>
        </div>

        <!-- Tabs for Course and Section Views -->
        <div x-data="{ activeTab: 'course' }">
            <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'course'" :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'course', 'border-transparent text-gray-500 dark:text-gray-400': activeTab !== 'course' }" class="py-2 px-4 text-center border-b-2 font-medium text-sm">
                        By Course
                    </button>
                    <button @click="activeTab = 'section'" :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'section', 'border-transparent text-gray-500 dark:text-gray-400': activeTab !== 'section' }" class="py-2 px-4 text-center border-b-2 font-medium text-sm">
                        By Section
                    </button>
                </nav>
            </div>

            <!-- Course Tab -->
            <div x-show="activeTab === 'course'" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Course
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Collected
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Pending
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($finesByCourse as $course)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $course->course_name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">
                                    ₱{{ number_format($course->collected, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400">
                                    ₱{{ number_format($course->pending, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    ₱{{ number_format($course->collected + $course->pending, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No fines data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Section Tab -->
            <div x-show="activeTab === 'section'" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Course
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Section
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Collected
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Pending
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($finesBySection as $section)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $section->course_name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $section->section_name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">
                                    ₱{{ number_format($section->collected, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400">
                                    ₱{{ number_format($section->pending, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    ₱{{ number_format($section->collected + $section->pending, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    No fines data available
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
