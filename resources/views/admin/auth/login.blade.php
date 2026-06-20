<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login Admin PMB</title>

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        <main class="flex min-h-screen items-center justify-center px-5 py-10">
            <section class="w-full max-w-md rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/70">
                <div class="flex items-center gap-3">
                    @if ($campusSetting?->logo_url)
                        <img src="{{ $campusSetting->logo_url }}" alt="{{ $campusSetting->campus_name }}" class="h-12 w-12 rounded-2xl object-contain ring-1 ring-slate-200">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-blue-100">
                            PMB
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-bold text-slate-950">{{ $campusSetting?->campus_name ?? 'Admin PMB' }}</p>
                        <p class="text-xs text-slate-500">Custom Admin Panel</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h1 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Masuk Admin</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Gunakan akun admin untuk mengelola setting kampus.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        @error('email')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                        <div class="relative mt-2">
                            <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-4 pr-24 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <button id="toggle-password" type="button" class="absolute inset-y-0 right-4 my-auto text-xs font-bold text-blue-700 transition hover:text-blue-800">
                                Show
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                        Ingat saya
                    </label>

                    <button type="submit" class="w-full rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                        Masuk
                    </button>
                </form>
            </section>
        </main>
        <script>
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('toggle-password');

            togglePasswordButton?.addEventListener('click', () => {
                const isPasswordVisible = passwordInput.type === 'text';

                passwordInput.type = isPasswordVisible ? 'password' : 'text';
                togglePasswordButton.textContent = isPasswordVisible ? 'Show' : 'Hidden';
            });
        </script>
    </body>
</html>
