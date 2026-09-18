<div>
    <div class="mb-4 flex flex-wrap gap-4 items-center">
        <select wire:model.live="filterDataset" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm font-medium">
            <option value="0">All Datasets</option>
            @foreach($this->datasets as $ds)
                <option value="{{ $ds->id }}">{{ $ds->name }} ({{ number_format($ds->images_count) }})</option>
            @endforeach
        </select>
    </div>

    @if(!empty($selected))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg flex items-center justify-between">
            <span class="text-sm text-red-700 dark:text-red-300">{{ count($selected) }} selected</span>
            <button wire:click="deleteSelected"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                Delete Metadata
            </button>
        </div>
    @endif

    <div class="mb-4 flex items-center gap-4">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <input type="checkbox" wire:model.live="selectAll" wire:change="toggleSelectAll"
                   class="rounded border-gray-300 text-blue-600">
            Select All Visible
        </label>
        <button wire:click="refreshFileStatus" wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 inline-flex items-center gap-2">
            <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            Refresh File Status
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($images as $image)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-start gap-3">
                    <input type="checkbox" value="{{ $image->id }}"
                           wire:click="toggleSelect('{{ $image->id }}')"
                           @if(in_array((string) $image->id, $selected)) checked @endif
                           class="mt-1 rounded border-gray-300 text-blue-600">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-red-500">&#10060;</span>
                            <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $image->filename }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono break-all">{{ $image->filepath }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                {{ $image->label === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ $image->label }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $image->fish_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                No missing files found. All files are present on storage.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $images->links() }}
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" wire:click="$set('showDeleteModal', false)"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Delete Metadata</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Delete metadata for {{ count($selected) }} image(s)? The files are already missing from storage.
                    </p>
                    <div class="mt-4 flex gap-3 justify-end">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button wire:click="confirmDelete"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                            Delete Metadata
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
