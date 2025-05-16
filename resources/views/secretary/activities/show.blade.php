<div class="mb-4">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Activity Details</h3>
    <dl class="mt-2 grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
        <div class="sm:col-span-1">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $activity->title }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $activity->description }}</dd>
        </div>
        <div class="sm:col-span-1">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $activity->due_date->format('M d, Y') }}</dd>
        </div>
    </dl>
</div> 