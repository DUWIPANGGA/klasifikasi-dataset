<x-app-layout>
    <div class="mb-6">
        <a href="{{ route('images.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm">&larr; Back to Images</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $image->filename }}" class="max-w-full max-h-full object-contain" loading="lazy">
                    @else
                        <div class="text-center text-gray-400">
                            <svg class="w-24 h-24 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm">{{ $image->filename }}</p>
                            <p class="mt-1 text-xs text-red-400">No image available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Details</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Filename</dt>
                        <dd class="text-gray-900 dark:text-white font-mono">{{ $image->filename }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Fish Name</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $image->fish_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Common Name</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $image->common_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Label</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $image->label === 'healthy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ $image->label }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Dimensions</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $image->width ?? '?' }} x {{ $image->height ?? '?' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Hash</dt>
                        <dd class="text-gray-900 dark:text-white font-mono text-xs break-all">{{ $image->hash }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $image->status->value === 'active' ? 'bg-green-100 text-green-800' : ($image->status->value === 'review' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $image->status->label() }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">File Status</dt>
                        <dd>
                            @if($fileExists)
                                <span class="text-green-600 dark:text-green-400 text-sm">&#10003; File exists</span>
                            @else
                                <span class="text-red-600 dark:text-red-400 text-sm">&#9888; File missing</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Filepath</dt>
                        <dd class="text-gray-900 dark:text-white font-mono text-xs break-all">{{ $image->filepath }}</dd>
                    </div>
                    @if($image->source)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Source</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $image->source }}</dd>
                    </div>
                    @endif
                    @if($image->downloaded_at)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Downloaded At</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $image->downloaded_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
