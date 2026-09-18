<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Fish Dataset Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        .brand-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        }
        .brand-gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .fish-pattern {
            opacity: 0.05;
        }
    </style>
</head>
<body class="h-full login-bg">
    <div class="min-h-screen flex">
        <!-- Left Panel - Brand -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden items-center justify-center">
            <div class="absolute inset-0 brand-gradient opacity-10"></div>
            <svg class="fish-pattern absolute w-full h-full" viewBox="0 0 800 600" fill="white">
                <circle cx="200" cy="150" r="80" />
                <circle cx="600" cy="400" r="120" />
                <circle cx="400" cy="300" r="60" />
                <circle cx="100" cy="450" r="90" />
                <circle cx="700" cy="150" r="70" />
                <ellipse cx="300" cy="500" rx="150" ry="80" />
                <ellipse cx="550" cy="100" rx="100" ry="60" />
            </svg>
            <div class="relative z-10 text-center px-12">
                <div class="w-24 h-24 brand-gradient rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-blue-500/25">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-4">Fish Dataset Manager</h1>
                <p class="text-xl text-slate-300 leading-relaxed">Kelola dataset ikan dengan mudah.<br>Impor, ekspor, dan deteksi duplikat dalam satu tempat.</p>

            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-10">
                    <div class="w-16 h-16 brand-gradient rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">Fish Dataset Manager</h2>
                </div>

                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-white">Selamat Datang</h2>
                        <p class="text-slate-400 mt-1">Masuk untuk melanjutkan ke dashboard</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-red-300">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                       placeholder="nama@email.com"
                                       class="input-focus w-full pl-11 pr-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition-all @error('email') border-red-500/50 @enderror">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" required
                                       placeholder="Masukkan password"
                                       class="input-focus w-full pl-11 pr-4 py-3 bg-slate-900/50 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition-all @error('password') border-red-500/50 @enderror">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input id="remember" name="remember" type="checkbox"
                                       class="w-4 h-4 bg-slate-900/50 border-slate-600/50 rounded text-blue-500 focus:ring-blue-500/30 focus:ring-offset-0 cursor-pointer">
                                <span class="text-sm text-slate-400 group-hover:text-slate-300 transition-colors">Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full py-3 px-4 brand-gradient hover:opacity-90 text-white font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 focus:ring-offset-slate-800 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40">
                            Masuk
                        </button>
                    </form>
                </div>

                <p class="text-center text-slate-500 text-xs mt-6">
                    Fish Dataset Manager v1.0
                </p>
            </div>
        </div>
    </div>
</body>
</html>
