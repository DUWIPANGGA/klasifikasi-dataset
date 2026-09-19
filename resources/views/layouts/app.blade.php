<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fish Dataset Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    <div class="flex h-full">
        <x-sidebar />
        <main class="flex-1 overflow-auto relative z-0">
            <div class="p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
