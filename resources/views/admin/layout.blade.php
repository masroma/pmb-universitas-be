<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin PMB')</title>

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:flex">
            <aside class="border-b border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:left-0 lg:w-72 lg:border-b-0 lg:border-r">
                <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-6">
                    @if ($campusSetting->logo_url)
                        <img src="{{ $campusSetting->logo_url }}" alt="{{ $campusSetting->campus_name }}" class="h-11 w-11 rounded-xl object-contain ring-1 ring-slate-200">
                    @else
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-blue-100">
                            PMB
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-bold leading-5">{{ $campusSetting->campus_name }}</p>
                        <p class="text-xs text-slate-500">Admin PMB</p>
                    </div>
                </div>

                @php
                    $adminUser = auth()->user();
                    $canManagePmb = $adminUser?->hasAdminRole('admin_pmb');
                    $canManageCrm = $adminUser?->hasAdminRole('admin_pmb', 'operator_crm');
                @endphp

                <nav class="flex gap-2 overflow-x-auto px-4 py-4 lg:block lg:space-y-2 lg:overflow-visible">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">DB</span>
                        Dashboard
                    </a>
                    @if ($canManageCrm)
                        <a href="{{ route('admin.ai-dashboard') }}" class="{{ request()->routeIs('admin.ai-dashboard') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">DA</span>
                            Dashboard AI
                        </a>
                    @endif
                    @if ($canManagePmb)
                        <a href="{{ route('admin.local-applications.index') }}" class="{{ request()->routeIs('admin.local-applications.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">PL</span>
                            Pendaftaran Lokal
                        </a>
                    @endif
                    @if ($canManageCrm)
                        <a href="{{ route('admin.ai-chat-leads.index') }}" class="{{ request()->routeIs('admin.ai-chat-leads.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">AI</span>
                            CRM AI
                        </a>
                    @endif
                    @if ($canManagePmb)
                        <a href="{{ route('admin.master-pmb.index', 'campuses') }}" class="{{ request()->routeIs('admin.master-pmb.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">MP</span>
                            Master PMB
                        </a>
                    @endif
                    @if ($canManagePmb)
                    <a href="{{ route('admin.pmb-catalog.opened-registrations') }}" class="{{ request()->routeIs('admin.pmb-catalog.opened-registrations') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">PD</span>
                        Pendaftaran Dibuka
                    </a>
                    <a href="{{ route('admin.pmb-catalog.applicants') }}" class="{{ request()->routeIs('admin.pmb-catalog.applicants') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">PN</span>
                        Pendaftar
                    </a>
                    <a href="{{ route('admin.pmb-catalog.study-programs') }}" class="{{ request()->routeIs('admin.pmb-catalog.study-programs') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">PS</span>
                        Program Studi
                    </a>
                    <a href="{{ route('admin.pmb-catalog.periods') }}" class="{{ request()->routeIs('admin.pmb-catalog.periods') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">PR</span>
                        Periode
                    </a>
                    <a href="{{ route('admin.pmb-information.index') }}" class="{{ request()->routeIs('admin.pmb-information.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">KP</span>
                        Konten PMB
                    </a>
                    <a href="{{ route('admin.pmb-cbt.index') }}" class="{{ request()->routeIs('admin.pmb-cbt.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">CT</span>
                        Kelola CBT
                    </a>
                    <a href="{{ route('admin.master-pmb.index', 'tuition-fees') }}" class="{{ request()->routeIs('admin.tuition-fees.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">BK</span>
                        Biaya Kuliah
                    </a>
                    @endif
                </nav>
            </aside>

            <div class="min-h-screen flex-1 lg:pl-72">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                    <div class="flex h-20 items-center justify-between px-5 sm:px-8">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">Admin Panel</p>
                            <h1 class="mt-1 text-xl font-bold tracking-[-0.02em] text-slate-950">@yield('page_title', 'Dashboard')</h1>
                        </div>

                        <div class="relative" id="admin-user-menu">
                            <button
                                type="button"
                                id="admin-user-menu-button"
                                class="flex items-center gap-3 rounded-2xl bg-transparent px-1 py-1 transition hover:bg-slate-50"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <div class="hidden text-right sm:block">
                                    <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-blue-100">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                id="admin-user-menu-dropdown"
                                class="absolute right-0 z-30 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-lg"
                            >
                                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074 0z" /></svg>
                                    Edit Profile
                                </a>
                                <a href="{{ route('admin.profile.password.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                    Edit Password
                                </a>
                                @if ($canManagePmb)
                                    <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>
                                        Setting Website
                                    </a>
                                @endif
                                <div class="my-1 border-t border-slate-100"></div>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                                        <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z" clip-rule="evenodd" /></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="px-5 py-8 sm:px-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            (function () {
                const menu = document.getElementById('admin-user-menu');
                const button = document.getElementById('admin-user-menu-button');
                const dropdown = document.getElementById('admin-user-menu-dropdown');

                if (!menu || !button || !dropdown) {
                    return;
                }

                const closeMenu = () => {
                    dropdown.classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                };

                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const isOpen = !dropdown.classList.contains('hidden');
                    if (isOpen) {
                        closeMenu();
                    } else {
                        dropdown.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!menu.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMenu();
                    }
                });
            })();
        </script>
    </body>
</html>
