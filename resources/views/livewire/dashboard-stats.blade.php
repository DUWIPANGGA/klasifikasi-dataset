<div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Images</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_images']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Healthy</p>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($stats['healthy']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Disease</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($stats['disease']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unique</p>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($stats['unique_hashes']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Duplicates</p>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ number_format($stats['duplicates']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cross-label Duplicates</p>
            <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ number_format($stats['cross_label_duplicates']) }}</p>
        </div>
    </div>
</div>
