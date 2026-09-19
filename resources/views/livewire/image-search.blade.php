<div>
    @if($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" wire:click="close"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-5xl w-full max-h-[90vh] flex flex-col">

                    {{-- Header --}}
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Search & Import Images</h3>
                            <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {{-- Search bar --}}
                        <div class="flex gap-2">
                            <input type="text" wire:model="query" wire:keydown.enter="search"
                                   placeholder="Search fish images... (e.g., cupang anchor worm, betta fish disease)"
                                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <button wire:click="search" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50">
                                Search
                            </button>
                        </div>

                        {{-- Filters --}}
                        <div class="flex gap-3 mt-3">
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Import to Dataset</label>
                                <select wire:model="filterDataset" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="0">-- Select Dataset --</option>
                                    @foreach($this->datasets as $ds)
                                        <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Label (auto)</label>
                                <input type="text" wire:model="filterLabel" placeholder="e.g., anchor_worm"
                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Fish Name (auto)</label>
                                <input type="text" wire:model="filterFish"
                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>

                    {{-- Results --}}
                    <div class="flex-1 overflow-y-auto p-4">
                        @if($searching)
                            <div class="text-center py-12">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                <p class="mt-2 text-sm text-gray-500">Searching...</p>
                            </div>
                        @elseif($status)
                            <div class="text-center py-8">
                                <p class="text-sm text-yellow-600 dark:text-yellow-400">{{ $status }}</p>
                            </div>
                        @elseif(empty($results))
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <p>Search for fish disease images to import</p>
                            </div>
                        @else
                            {{-- Selection bar --}}
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex gap-2">
                                    <button wire:click="selectAll" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">Select All</button>
                                    <button wire:click="deselectAll" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Deselect All</button>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($selected) }} selected / {{ count($results) }} results</span>
                            </div>

                            {{-- Image grid --}}
                            <div class="grid grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                                @foreach($results as $index => $image)
                                    @php $origUrl = $image['original_url'] ?? $image['url']; @endphp
                                    <div wire:click="toggleSelect('{{ addslashes($origUrl) }}')"
                                         class="relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden cursor-pointer border-2 transition-all duration-150
                                         {{ $this->isSelected($origUrl) ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-800' : 'border-transparent hover:border-gray-300 dark:hover:border-gray-600' }}">
                                        <img src="{{ $image['thumbnail'] }}" alt="" class="w-full h-full object-cover" loading="lazy"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <div class="w-full h-full items-center justify-center text-gray-400" style="display:none">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        @if($this->isSelected($origUrl))
                                            <div class="absolute inset-0 bg-blue-500/20 flex items-center justify-center">
                                                <div class="bg-blue-600 rounded-full p-1 shadow-lg">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-1.5">
                                            <p class="text-white text-[10px] truncate">{{ $image['author'] ?: $image['source'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if($importCount > 0)
                                <span class="text-green-600 dark:text-green-400 font-medium">✓ {{ $importCount }} image(s) imported</span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="close" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">Close</button>
                            <button wire:click="importSelected" wire:loading.attr="disabled"
                                    @disabled(empty($selected) || $filterDataset === 0 || $importing)
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                                @if($importing)
                                    Importing...
                                @else
                                    Import {{ count($selected) }} Image(s)
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
