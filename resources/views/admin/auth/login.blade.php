<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login Admin PMB</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        <style>
            body {
                font-family: Manrope, ui-sans-serif, system-ui, sans-serif;
                background:
                    radial-gradient(1000px 520px at 15% 10%, rgba(196, 165, 116, 0.22), transparent 55%),
                    radial-gradient(800px 480px at 90% 90%, rgba(28, 39, 64, 0.35), transparent 50%),
                    linear-gradient(145deg, #070B14 0%, #0C1222 45%, #141C2E 100%);
            }
            .font-display { font-family: "Cormorant Garamond", Georgia, serif; }
        </style>
    </head>
    <body class="text-slate-900 antialiased">
        <main class="relative flex min-h-screen items-center justify-center px-5 py-10">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -left-20 top-16 h-64 w-64 rounded-full bg-[#C4A574]/10 blur-3xl"></div>
                <div class="absolute bottom-10 right-10 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
            </div>

            <section class="relative w-full max-w-md overflow-hidden rounded-[2rem] border border-white/10 bg-white/95 p-8 shadow-[0_40px_100px_-40px_rgba(0,0,0,0.7)] backdrop-blur-xl">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#E8D5B5] via-[#C4A574] to-[#A88754]"></div>

                <div class="flex items-center gap-3">
                    @if ($campusSetting?->logo_url)
                        <img src="{{ $campusSetting->logo_url }}" alt="{{ $campusSetting->campus_name }}" class="h-12 w-12 rounded-2xl object-contain ring-1 ring-[#C4A574]/30 bg-white p-1">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#E8D5B5] to-[#C4A574] text-sm font-extrabold text-[#0C1222]">
                            PMB
                        </div>
                    @endif
                    <div>
                        <p class="font-display text-xl font-semibold leading-none text-[#0C1222]">{{ $campusSetting?->campus_name ?? 'Admin PMB' }}</p>
                        <p class="mt-1.5 text-[11px] font-bold uppercase tracking-[0.2em] text-[#A88754]">Private Admin Access</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h1 class="font-display text-3xl font-semibold tracking-[-0.02em] text-[#0C1222]">Masuk Admin</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Akses panel pengelolaan PMB dengan akun admin resmi.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-2xl border border-slate-200 bg-[#F7F8FA] px-4 py-3 text-sm outline-none transition focus:border-[#C4A574] focus:bg-white focus:ring-4 focus:ring-[#C4A574]/15">
                        @error('email')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                        <div class="relative mt-2">
                            <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-[#F7F8FA] py-3 pl-4 pr-24 text-sm outline-none transition focus:border-[#C4A574] focus:bg-white focus:ring-4 focus:ring-[#C4A574]/15">
                            <button id="toggle-password" type="button" class="absolute inset-y-0 right-4 my-auto text-xs font-bold text-[#A88754] transition hover:text-[#0C1222]">
                                Show
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#C4A574] focus:ring-[#C4A574]">
                        Ingat saya
                    </label>

                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-[#1C2740] to-[#0C1222] px-5 py-3.5 text-sm font-bold text-white shadow-[0_18px_40px_-20px_rgba(12,18,34,0.9)] transition hover:from-[#243352] hover:to-[#141C2E]">
                        Masuk ke Panel
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
                togglePasswordButton.textContent = isPasswordVisible ? 'Show' : 'Hide';
            });
        </script>
    </body>
</html>
