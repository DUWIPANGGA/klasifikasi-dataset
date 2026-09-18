<div>
    {{-- Step 1: Upload --}}
    @if($step === 1)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Step 1: Upload CSV</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">CSV File</label>
                    <input type="file" wire:model.live="csvFile" accept=".csv,.txt"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-300">
                </div>
                @error('csvFile') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @if($csvFile)
                    <button wire:click="uploadCsv"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Upload & Preview
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Step 2: Preview + Select Dataset --}}
    @if($step === 2)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Step 2: Preview & Configure</h2>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Dataset</label>
                <div class="flex gap-4">
                    <select wire:model.live="selectedDatasetId" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <option value="0">-- Create New Dataset --</option>
                        @foreach($this->datasets as $ds)
                            <option value="{{ $ds->id }}">{{ $ds->name }} ({{ $ds->images_count }} images)</option>
                        @endforeach
                    </select>
                </div>
                @if($selectedDatasetId === 0)
                    <div class="mt-3 grid grid-cols-2 gap-4">
                        <input type="text" wire:model="newDatasetName" placeholder="Dataset name"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                        <input type="text" wire:model="newDatasetDescription" placeholder="Description (optional)"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm">
                    </div>
                @endif
            </div>

            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview (first {{ count($previewData) }} rows)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                @if(!empty($previewData[0]))
                                    @foreach(array_keys($previewData[0]) as $header)
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">{{ $header }}</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($previewData as $row)
                                <tr>
                                    @foreach($row as $val)
                                        <td class="px-3 py-2 text-gray-900 dark:text-gray-300 truncate max-w-[150px]" title="{{ $val }}">{{ $val }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex gap-3">
                <button wire:click="$set('step', 1)" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm">Back</button>
                <button wire:click="startImport" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">Start Import</button>
            </div>
        </div>
    @endif

    {{-- Step 3: Importing --}}
    @if($step === 3)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Importing dataset...</p>
        </div>
    @endif

    {{-- Step 4: Results --}}
    @if($step === 4)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Import Complete</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($importStats['total'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total CSV</p>
                </div>
                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($importStats['imported'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Imported</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($importStats['already_exists'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Already Exists</p>
                </div>
                <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($importStats['invalid'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Invalid</p>
                </div>
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($importStats['missing_files'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Missing Files</p>
                </div>
            </div>
            <button wire:click="resetWizard" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Import Another</button>
        </div>
    @endif
</div>
