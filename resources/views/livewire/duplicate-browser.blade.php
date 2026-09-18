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
                <button wire:click="deleteSelected"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    Delete Selected ({{ count($selectedImages) }})
                </button>
            @endif
            <button wire:click="rebuildDuplicates"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                Rebuild Duplicates
            </button>
        </div>
    </div>

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
                        <div class="relative w-40 bg-gray-50 dark:bg-gray-700 rounded-lg p-3 border-2 transition-colors
                            {{ in_array((string) $image->id, array_keys($selectedImages)) ? 'border-blue-500' : 'border-transparent' }}">
                            <input type="checkbox"
                                   wire:click="toggleImage({{ $image->id }})"
                                   @if(isset($selectedImages[(string) $image->id])) checked @endif
                                   class="absolute top-2 left-2 rounded border-gray-300 text-blue-600">
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

    <div class="mt-6">
        {{ $groups->links() }}
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
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
                        <button wire:click="confirmDelete"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
