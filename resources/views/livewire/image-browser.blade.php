<div x-data="{
    ctx: false, ctxX: 0, ctxY: 0, ctxId: null, ctxLabel: '', ctxDataset: null, ctxFishName: '',
    editingLabel: false, editVal: '', editDataset: null, editFishName: '',
    close() { this.ctx = false; this.editingLabel = false; },
    init() {
        this.$watch('ctx', (v) => {
            if (v) {
                setTimeout(() => {
                    const handler = (e) => {
                        if (!e.target.closest('.ctx-menu')) this.close();
                        document.removeEventListener('click', handler);
                    };
                    document.addEventListener('click', handler);
                }, 0);
            }
        });
        this.$refs.labelInput && this.$refs.labelInput.focus();
    },
    handleQuickLabel(e) {
        const count = @js(count($selected));
        if (count === 0) return;
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
        const key = e.key;
        const labels = @js($this->labels);
        if (key === '1' && labels.length > 0) $wire.quickLabel(labels[0]);
        else if (key === '2' && labels.length > 1) $wire.quickLabel(labels[1]);
        else if (key === '3' && labels.length > 2) $wire.quickLabel(labels[2]);
        else if (key === '4' && labels.length > 3) $wire.quickLabel(labels[3]);
        else if (key === '5' && labels.length > 4) $wire.quickLabel(labels[4]);
        else if (key === '6' && labels.length > 5) $wire.quickLabel(labels[5]);
        else if (key === '7' && labels.length > 6) $wire.quickLabel(labels[6]);
        else if (key === '8' && labels.length > 7) $wire.quickLabel(labels[7]);
        else if (key === '9' && labels.length > 8) $wire.quickLabel(labels[8]);
    }
}" x-on:keydown.escape.window="close()" x-on:keydown.window="handleQuickLabel($event)">

    {{-- Context Menu --}}
    <template x-if="ctx" x-teleport="body">
        <div class="fixed z-[9999] ctx-menu" :style="'left:' + ctxX + 'px; top:' + ctxY + 'px'"
             @click.prevent @contextmenu.prevent>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 py-1 min-w-[140px]">
                <template x-if="!editingLabel">
                    <div>
                        <button @click="editingLabel = true; editVal = ctxLabel; editDataset = ctxDataset; editFishName = ctxFishName"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit
                        </button>
                        <button @click="close(); $wire.deleteSingle(ctxId)"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Delete
                        </button>
                    </div>
                </template>
                <template x-if="editingLabel">
                    <div class="p-2 space-y-2">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 px-1">Label:</p>
                            <select x-model="editVal"
                                    class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1.5">
                                @foreach($this->labels as $l)
                                    <option value="{{ $l }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 px-1">Jenis Ikan:</p>
                            <input type="text" x-model="editFishName"
                                   class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1.5"
                                   placeholder="Fish species name...">
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 px-1">Dataset:</p>
                            <select x-model="editDataset"
                                    class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1.5">
                                @foreach($this->datasets as $ds)
                                    <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-1">
                            <button @click="close(); $wire.updateImage(ctxId, editVal, editFishName, editDataset)"
                                    class="flex-1 px-2 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 font-medium">Save</button>
                            <button @click="editingLabel = false"
                                    class="px-2 py-1.5 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs rounded hover:bg-gray-400">Cancel</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Filters --}}
    <div class="mb-6 flex flex-wrap gap-3 items-center">
        <select wire:model.live="filterDataset" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm font-medium">
            <option value="0">All Datasets</option>
            @foreach($this->datasets as $ds)
                <option value="{{ $ds->id }}">{{ $ds->name }} ({{ number_format($ds->images_count) }})</option>
            @endforeach
        </select>
        <div class="flex-1 min-w-[200px]">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search filename, hash, fish name, label..."
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
        <select wire:model.live="filterDuplicate" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm">
            <option value="">All Images</option>
            <option value="duplicate">Has Duplicates</option>
            <option value="cross_label">Cross-label Dup</option>
            <option value="unique">No Duplicates</option>
        </select>
    </div>

    {{-- Selection Bar --}}
    @if(!empty($selected))
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-between">
            <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">{{ count($selected) }} image(s) selected</span>
            <div class="flex gap-2">
                <button wire:click="openBulkEditModal"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    Edit Selected
                </button>
                <button wire:click="deleteSelected"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    Delete Selected
                </button>
                <button wire:click="clearSelected"
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-400">
                    Clear
                </button>
            </div>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="mb-4 flex items-center gap-4">
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" wire:click="toggleSelectAll"
                       @if($allPageSelected) checked @endif
                       class="rounded border-gray-300 text-blue-600">
                Page ({{ count($images) }})
            </label>
            @if($allMatchingCount > count($images))
                <label class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 cursor-pointer font-medium">
                    <input type="checkbox" wire:click="toggleSelectAllMatching"
                           @if($allMatchingCount === count($selected)) checked @endif
                           class="rounded border-gray-300 text-blue-600">
                    All ({{ number_format($allMatchingCount) }})
                </label>
            @endif
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ number_format($total) }} images
        </span>
        <div class="flex-1"></div>
        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
            <span class="bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded font-mono">1-9</span> Quick Label
            @if(!empty($selected))
                <span class="text-gray-300 dark:text-gray-600">|</span>
                @foreach(array_slice($this->labels, 0, 9) as $i => $label)
                    <span class="bg-gray-200 dark:bg-gray-700 px-1 py-0.5 rounded font-mono">{{ $i + 1 }}</span>
                    <span class="text-gray-500 dark:text-gray-400">{{ $label }}</span>
                    @if($i < min(8, count($this->labels) - 1)) <span class="text-gray-300 dark:text-gray-600">·</span> @endif
                @endforeach
            @endif
        </div>
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

    {{-- Image Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4"
         x-data="{ lastIdx: -1 }"
         @click="lastIdx = -1">
        @forelse($images as $idx => $image)
            <div x-data="{ sel: {{ in_array((string) $image->id, $selected) ? 'true' : 'false' }} }"
                 @click.prevent.stop="
                    if ($event.button !== 0) return;
                    if ($event.shiftKey && lastIdx >= 0) {
                        let items = $el.parentElement.children;
                        let start = Math.min(lastIdx, {{ $idx }});
                        let end = Math.max(lastIdx, {{ $idx }});
                        let ids = [];
                        for (let i = start; i <= end; i++) {
                            let card = items[i];
                            if (card && card.dataset.id) {
                                ids.push(card.dataset.id);
                                card.classList.add('ring-2', 'ring-blue-200', 'dark:ring-blue-800', 'border-blue-500', 'dark:border-blue-400');
                            }
                        }
                        $wire.selectRange(ids);
                    } else {
                        sel = !sel;
                        $wire.toggleSelect('{{ $image->id }}');
                    }
                    lastIdx = {{ $idx }};
                 "
                 data-id="{{ $image->id }}"
                 @contextmenu.prevent.stop="ctx = true; ctxX = $event.clientX; ctxY = $event.clientY; ctxId = {{ $image->id }}; ctxLabel = '{{ $image->label }}'; ctxDataset = {{ $image->dataset_id }}; ctxFishName = '{{ $image->fish_name }}'"
                 :class="sel ? 'border-blue-500 dark:border-blue-400 ring-2 ring-blue-200 dark:ring-blue-800' : 'border-gray-200 dark:border-gray-700'"
                 class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border overflow-hidden group hover:shadow-md transition-all duration-150 cursor-pointer select-none">
                <div class="relative aspect-square bg-gray-100 dark:bg-gray-700">
                    <template x-if="sel">
                        <div class="absolute inset-0 bg-blue-500/20 flex items-center justify-center z-[5]">
                            <div class="bg-blue-600 rounded-full p-1.5 shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                    </template>
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
                           wire:click.stop
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

    {{-- Load More --}}
    @if($hasMorePages)
        <div class="py-8 text-center">
            <button wire:click="loadMore"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                Load More Images
            </button>
        </div>
    @else
        <div class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">
            All {{ number_format($total) }} images loaded
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[9998] overflow-y-auto" aria-modal="true" x-data>
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

    {{-- Add Image Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 z-[9998] overflow-y-auto" aria-modal="true" x-data>
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

    {{-- Bulk Add Modal --}}
    @if($showBulkModal)
        <div class="fixed inset-0 z-[9998] overflow-y-auto" aria-modal="true" x-data>
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
                                               class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <button type="button" wire:click="$set('bulkLabel', '')"
                                                class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                    </div>
                                @else
                                    <select wire:model.live="bulkLabel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Select --</option>
                                        @foreach($this->labels as $l)
                                            <option value="{{ $l }}">{{ $l }}</option>
                                        @endforeach
                                        <option value="__new__">+ Add New...</option>
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

    {{-- Bulk Edit Modal --}}
    @if($showBulkEditModal)
        <div class="fixed inset-0 z-[9998] overflow-y-auto" aria-modal="true" x-data>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" wire:click="$set('showBulkEditModal', false)"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit {{ count($selected) }} Image(s)</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kosongkan field yang tidak ingin diubah.</p>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                            <select wire:model="bulkEditLabel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Tidak diubah --</option>
                                @foreach($this->labels as $l)
                                    <option value="{{ $l }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Ikan</label>
                            <input type="text" wire:model="bulkEditFishName" placeholder="-- Tidak diubah --"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dataset</label>
                            <select wire:model="bulkEditDatasetId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="0">-- Tidak diubah --</option>
                                @foreach($this->datasets as $ds)
                                    <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select wire:model="bulkEditStatus" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Tidak diubah --</option>
                                <option value="active">Active</option>
                                <option value="review">Review</option>
                                <option value="deleted">Deleted</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3 justify-end">
                        <button wire:click="$set('showBulkEditModal', false)"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button wire:click="applyBulkEdit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            Apply Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
