<div>
    {{-- Step 1: Upload --}}
    @if($step === 1)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upload CSV Files</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">CSV Files (up to 50)</label>
                    <input type="file" wire:model="csvFiles" accept=".csv,.txt" multiple
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple files</p>
                </div>
                @error('csvFiles') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @error('csvFiles.*') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                @if(!empty($csvFiles) && count($csvFiles) > 0)
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">{{ count($csvFiles) }} file(s) selected</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Import Mode</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="importMode" value="auto" class="text-blue-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Auto: 1 dataset per CSV (named after file)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="importMode" value="single" class="text-blue-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Single: all CSVs into one dataset</span>
                            </label>
                        </div>
                    </div>

                    @if($importMode === 'single')
                        <div class="flex gap-4">
                            <select wire:model.live="selectedDatasetId" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                <option value="0">-- Create New Dataset --</option>
                                @foreach($this->datasets as $ds)
                                    <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($selectedDatasetId === 0)
                            <div class="flex gap-4">
                                <input type="text" wire:model="newDatasetName" placeholder="Dataset name"
                                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                                <input type="text" wire:model="newDatasetDescription" placeholder="Description (optional)"
                                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                            </div>
                        @endif
                    @endif

                    <button wire:click="uploadCsv"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Upload & Preview All
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Step 2: Preview --}}
    @if($step === 2)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview ({{ count($filePreviews) }} files)</h2>

            <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                @foreach($filePreviews as $index => $file)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $file['name'] }}</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($file['total_rows']) }} rows</span>
                        </div>
                        @if(!empty($file['preview']))
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700">
                                            @foreach($file['headers'] as $header)
                                                <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($file['preview'] as $row)
                                            <tr>
                                                @foreach($row as $val)
                                                    <td class="px-2 py-1 text-gray-700 dark:text-gray-300 truncate max-w-[120px]" title="{{ $val }}">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex gap-3">
                <button wire:click="$set('step', 1)" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">Back</button>
                <button wire:click="startImport" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    Import All {{ count($filePreviews) }} Files
                </button>
            </div>
        </div>
    @endif

    {{-- Step 3: Importing --}}
    @if($step === 3)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Importing file {{ $currentFileIndex }} of {{ $totalFiles }}...</p>
        </div>
    @endif

    {{-- Step 4: Results --}}
    @if($step === 4)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Import Complete ({{ count($importResults) }} files)</h2>

            <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                @foreach($importResults as $result)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $result['file_name'] }}</h3>
                            <span class="text-xs text-blue-600 dark:text-blue-400">→ {{ $result['dataset_name'] }}</span>
                        </div>
                        <div class="grid grid-cols-5 gap-2 text-center text-xs">
                            <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                <p class="font-bold text-gray-900 dark:text-white">{{ number_format($result['total'] ?? 0) }}</p>
                                <p class="text-gray-500">Total</p>
                            </div>
                            <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                <p class="font-bold text-green-600">{{ number_format($result['imported'] ?? 0) }}</p>
                                <p class="text-gray-500">Imported</p>
                            </div>
                            <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                                <p class="font-bold text-yellow-600">{{ number_format($result['already_exists'] ?? 0) }}</p>
                                <p class="text-gray-500">Exists</p>
                            </div>
                            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded">
                                <p class="font-bold text-red-600">{{ number_format($result['invalid'] ?? 0) }}</p>
                                <p class="text-gray-500">Invalid</p>
                            </div>
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded">
                                <p class="font-bold text-purple-600">{{ number_format($result['duplicates'] ?? 0) }}</p>
                                <p class="text-gray-500">Dups</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-center">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format(array_sum(array_column($importResults, 'total'))) }}</p>
                    <p class="text-xs text-gray-500">Total Rows</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-green-600">{{ number_format(array_sum(array_column($importResults, 'imported'))) }}</p>
                    <p class="text-xs text-gray-500">Imported</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-yellow-600">{{ number_format(array_sum(array_column($importResults, 'already_exists'))) }}</p>
                    <p class="text-xs text-gray-500">Skipped</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-red-600">{{ number_format(array_sum(array_column($importResults, 'invalid'))) }}</p>
                    <p class="text-xs text-gray-500">Invalid</p>
                </div>
            </div>

            <button wire:click="resetWizard" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Import More</button>
        </div>
    @endif
</div>
