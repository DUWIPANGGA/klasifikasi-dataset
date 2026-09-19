<div>
    <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex gap-4 items-center">
            <select wire:model.live="filterDataset" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm font-medium">
                <option value="0">All Datasets</option>
                @foreach($this->datasets as $ds)
                    <option value="{{ $ds->id }}">{{ $ds->name }} ({{ number_format($ds->images_count) }})</option>
                @endforeach
            </select>
            <select wire:model.live="filterType" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm">
                <option value="">All Types</option>
                <option value="internal">Internal Duplicates</option>
                <option value="cross_label">Cross-label Duplicates</option>
            </select>
            <select wire:model.live="filterLabel" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm">
                <option value="">All Labels</option>
                @foreach($this->labels as $label)
                    <option value="{{ $label }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            @if(!empty($selectedImages))
                <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">{{ count($selectedImages) }} selected</span>
                <button wire:click="deleteSelected"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Delete Selected
                </button>
            @endif
            <button wire:click="rebuildDuplicates"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                Rebuild Duplicates
            </button>
        </div>
    </div>

    @if($deleteError)
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <p class="text-sm text-red-700 dark:text-red-300">{{ $deleteError }}</p>
            </div>
            <button wire:click="$set('deleteError', null)" class="text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    @forelse($groups as $group)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-4 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-sm text-gray-500 dark:text-gray-400">Group #{{ $group->id }}</span>
                    @if($group->isCrossLabel())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                            &#9888; CROSS-LABEL DUPLICATE
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                            Internal Duplicate
                        </span>
                    @endif
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Hash: <span class="font-mono">{{ substr($group->hash, 0, 12) }}...</span>
                    </span>
                </div>
                <div class="flex gap-2">
                    <button wire:click="selectAllInGroup({{ $group->id }})"
                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">Select All</button>
                    <button wire:click="deselectAllInGroup({{ $group->id }})"
                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Deselect All</button>
                </div>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-4">
                    @foreach($group->images as $image)
                        <div x-data="{ sel: {{ isset($selectedImages[(string) $image->id]) ? 'true' : 'false' }} }"
                             @click="sel = !sel; $wire.toggleImage({{ $image->id }})"
                             :class="sel ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-800 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent bg-gray-50 dark:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                             class="relative w-40 rounded-lg p-3 border-2 transition-all duration-150 cursor-pointer select-none">
                            <template x-if="sel">
                                <div class="absolute top-2 left-2 bg-blue-600 rounded-full p-1 z-10 shadow-lg">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </template>
                            <div class="w-full aspect-square bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center mt-2 overflow-hidden">
                                @if($image->thumbnail || $image->image_url)
                                    <img src="{{ $image->thumbnail ?: $image->image_url }}" alt="{{ $image->filename }}"
                                         class="w-full h-full object-cover" loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                    <div class="w-full h-full items-center justify-center text-gray-400" style="display:none">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 truncate" title="{{ $image->filename }}">{{ $image->filename }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium mt-1
                                {{ $image->label === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ $image->label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">No duplicate groups found.</p>
        </div>
    @endforelse

    <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        {{ number_format($total) }} duplicate groups
    </div>

    @if($hasMorePages)
        <div class="py-4 text-center">
            <button wire:click="loadMore"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Load More
            </button>
        </div>
    @else
        <div class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">
            All {{ number_format($total) }} groups loaded
        </div>
    @endif

    @if($showDeleteModal)
        <div class="fixed inset-0 z-[9998] overflow-y-auto" aria-modal="true" x-data>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" wire:click="$set('showDeleteModal', false)"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Delete</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Delete {{ count($selectedImages) }} selected image(s) from storage and metadata?
                    </p>
                    <div class="mt-4 flex gap-3 justify-end">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button wire:click="confirmDelete" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50 inline-flex items-center gap-2">
                            <svg wire:loading wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            <span wire:loading.remove>Delete</span>
                            <span wire:loading>Menghapus...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
