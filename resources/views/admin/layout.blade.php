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
                    <a href="{{ route('admin.master-pmb.index', 'tuition-fees') }}" class="{{ request()->routeIs('admin.tuition-fees.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">BK</span>
                        Biaya Kuliah
                    </a>
                    <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex min-w-fit items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-xs shadow-sm ring-1 ring-slate-200">ST</span>
                        Setting Kampus
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

                        <div class="flex items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="px-5 py-8 sm:px-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
