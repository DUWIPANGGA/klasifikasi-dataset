<div>
    <div class="mb-6 flex flex-wrap gap-4 items-center">
        <select wire:model.live="filterDataset" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm font-medium">
            <option value="0">All Datasets</option>
            @foreach($this->datasets as $ds)
                <option value="{{ $ds->id }}">{{ $ds->name }} ({{ number_format($ds->images_count) }})</option>
            @endforeach
        </select>
        <div class="flex-1 min-w-[200px]">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search filename, hash, fish name..."
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <select wire:model.live="filterLabel" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm">
            <option value="">All Labels</option>
            @foreach($this->labels as $label)
                <option value="{{ $label }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterFish" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm">
            <option value="">All Fish</option>
            @foreach($this->fishNames as $fish)
                <option value="{{ $fish }}">{{ $fish }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="review">Review</option>
            <option value="deleted">Deleted</option>
        </select>
    </div>

    @if(!empty($selected))
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
            <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">{{ count($selected) }} image(s) selected across pages</span>
            <div class="flex gap-2">
                <button wire:click="deleteSelected"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    Delete Selected
                </button>
                <button wire:click="clearSelected"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-400">
                    Clear Selection
                </button>
            </div>
        </div>
    @endif

    <div class="mb-4 flex items-center gap-4">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="checkbox" wire:click="toggleSelectAll"
                   @if($allPageSelected) checked @endif
                   class="rounded border-gray-300 text-blue-600">
            Select All on This Page
        </label>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $images->total() }} images
        </span>
        <div class="flex-1"></div>
        <button wire:click="openBulkModal"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Bulk Add
        </button>
        <button wire:click="openAddModal"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Image
        </button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        @forelse($images as $image)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden group hover:shadow-md transition-shadow">
                <div class="relative aspect-square bg-gray-100 dark:bg-gray-700">
                    <input type="checkbox" value="{{ $image->id }}"
                           wire:click="toggleSelect('{{ $image->id }}')"
                           @if(in_array((string) $image->id, $selected)) checked @endif
                           class="absolute top-2 left-2 z-10 rounded border-gray-300 text-blue-600">
                    @if($image->thumbnail || $image->image_url)
                        <img src="{{ $image->thumbnail ?: $image->image_url }}" alt="{{ $image->filename }}"
                             class="w-full h-full object-cover" loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="w-full h-full flex items-center justify-center text-gray-400" style="display:none">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    @if($image->isCrossLabelDuplicate())
                        <div class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-1.5 py-0.5 rounded font-medium">CROSS</div>
                    @elseif($image->isDuplicate())
                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-1.5 py-0.5 rounded font-medium">DUP</div>
                    @endif
                </div>
                <div class="p-3">
                    <p class="text-xs font-mono text-gray-500 dark:text-gray-400 truncate" title="{{ $image->filename }}">{{ $image->filename }}</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1 truncate">{{ $image->fish_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $image->common_name }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $image->label === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                            {{ $image->label }}
                        </span>
                        <a href="{{ route('images.show', $image) }}"
                           class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">View</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                No images found.
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
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Delete</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        You are about to delete {{ count($selected) }} image(s). This action will attempt to delete files from storage and soft-delete metadata.
                    </p>
                    <div class="mt-4 flex gap-3 justify-end">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button wire:click="confirmDelete(false)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                            Delete
                        </button>
                        <button wire:click="confirmDelete(true)"
                                class="px-4 py-2 bg-red-800 text-white rounded-lg text-sm hover:bg-red-900">
                            Force Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showAddModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" wire:click="$set('showAddModal', false)"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Image</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image URL *</label>
                            <input type="url" wire:model="addUrl" placeholder="https://drive.google.com/uc?export=view&id=..."
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('addUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label (Disease) *</label>
                            @if($addLabel === '__new__')
                                <div class="flex gap-2">
                                    <input type="text" wire:model="addNewLabel" placeholder="Type new label name..."
                                           class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <button type="button" wire:click="$set('addLabel', '')"
                                            class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                </div>
                            @else
                                <select wire:model.live="addLabel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Select Label --</option>
                                    @foreach($this->labels as $l)
                                        <option value="{{ $l }}">{{ $l }}</option>
                                    @endforeach
                                    <option value="__new__">+ Add New Label...</option>
                                </select>
                            @endif
                            @error('addLabel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @error('addNewLabel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3 justify-end">
                        <button wire:click="$set('showAddModal', false)"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button wire:click="addImage"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showBulkModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" wire:click="$set('showBulkModal', false)"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 max-w-2xl w-full shadow-xl max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bulk Add Images</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Image URLs <span class="text-gray-400">(one per line)</span>
                            </label>
                            <textarea wire:model="bulkUrls" rows="10"
                                      placeholder="https://drive.google.com/uc?export=view&id=abc123&#10;https://drive.google.com/uc?export=view&id=def456&#10;https://drive.google.com/uc?export=view&id=ghi789"
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"></textarea>
                            @if($bulkCount > 0)
                                <p class="text-sm text-purple-600 dark:text-purple-400 mt-1 font-medium">{{ $bulkCount }} URL(s) detected</p>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dataset *</label>
                                <select wire:model="bulkDatasetId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach($this->datasets as $ds)
                                        <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label (Disease) *</label>
                                @if($bulkLabel === '__new__')
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="bulkNewLabel" placeholder="Type new label..."
                                               class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <button type="button" wire:click="$set('bulkLabel', '')"
                                                class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                    </div>
                                @else
                                    <select wire:model.live="bulkLabel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Select Label --</option>
                                        @foreach($this->labels as $l)
                                            <option value="{{ $l }}">{{ $l }}</option>
                                        @endforeach
                                        <option value="__new__">+ Add New Label...</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        @error('bulkLabel') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                        @error('bulkNewLabel') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                        @error('bulkDatasetId') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                        @if($bulkResult)
                            <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm font-medium">
                                {{ $bulkResult }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 flex gap-3 justify-end">
                        <button wire:click="$set('showBulkModal', false)"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">
                            Close
                        </button>
                        <button wire:click="addBulkImages" wire:loading.attr="disabled"
                                disabled="{{ $bulkCount === 0 || $bulkProcessing }}"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            @if($bulkProcessing)
                                Processing...
                            @else
                                Add {{ $bulkCount }} Image(s)
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
