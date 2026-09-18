<div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 max-w-xl">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Configuration</h2>

        <form action="{{ route('export.download') }}" method="GET" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dataset</label>
                <select name="dataset_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                    <option value="">All Datasets</option>
                    @foreach($this->datasets as $ds)
                        <option value="{{ $ds->id }}">{{ $ds->name }} ({{ $ds->images_count }} images)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter</label>
                <select name="filter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                    <option value="active">Active Only</option>
                    <option value="all">All (including deleted)</option>
                    <option value="healthy">Healthy Only</option>
                    <option value="disease">Disease Only</option>
                </select>
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download CSV
                </button>
            </div>
        </form>
    </div>
</div>
