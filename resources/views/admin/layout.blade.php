<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin PMB')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            colors: {
                                ink: {
                                    950: '#070B14',
                                    900: '#0C1222',
                                    800: '#141C2E',
                                    700: '#1C2740',
                                },
                                champagne: {
                                    300: '#E8D5B5',
                                    400: '#D4B896',
                                    500: '#C4A574',
                                    600: '#A88754',
                                },
                            },
                            fontFamily: {
                                display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                                sans: ['Manrope', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            },
                            boxShadow: {
                                soft: '0 18px 50px -28px rgba(7, 11, 20, 0.45)',
                                glow: '0 0 0 1px rgba(196, 165, 116, 0.18), 0 20px 50px -30px rgba(196, 165, 116, 0.45)',
                            },
                        },
                    },
                };
            </script>
        @endif

        <style>
            :root {
                --ink-950: #070B14;
                --ink-900: #0C1222;
                --ink-800: #141C2E;
                --champagne: #C4A574;
                --champagne-soft: rgba(196, 165, 116, 0.14);
            }

            body.admin-shell {
                font-family: Manrope, ui-sans-serif, system-ui, sans-serif;
                background:
                    radial-gradient(1200px 600px at 12% -10%, rgba(196, 165, 116, 0.16), transparent 55%),
                    radial-gradient(900px 500px at 100% 0%, rgba(28, 39, 64, 0.08), transparent 45%),
                    linear-gradient(180deg, #F4F5F8 0%, #EBEEF3 100%);
                color: #0C1222;
            }

            .admin-sidebar {
                background:
                    radial-gradient(800px 420px at 10% 0%, rgba(196, 165, 116, 0.16), transparent 50%),
                    linear-gradient(180deg, #101828 0%, #0C1222 48%, #070B14 100%);
            }

            .admin-nav-link {
                display: flex;
                min-width: fit-content;
                align-items: center;
                gap: 0.75rem;
                border-radius: 1rem;
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: rgba(232, 236, 245, 0.72);
                transition: all 180ms ease;
                border: 1px solid transparent;
            }

            .admin-nav-link:hover {
                color: #F8FAFC;
                background: rgba(255, 255, 255, 0.05);
                border-color: rgba(255, 255, 255, 0.06);
            }

            .admin-nav-link.is-active {
                color: #0C1222;
                background: linear-gradient(135deg, #E8D5B5 0%, #C4A574 100%);
                border-color: rgba(232, 213, 181, 0.4);
                box-shadow: 0 12px 30px -18px rgba(196, 165, 116, 0.9);
            }

            .admin-nav-badge {
                display: flex;
                height: 2.25rem;
                width: 2.25rem;
                align-items: center;
                justify-content: center;
                border-radius: 0.8rem;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                background: rgba(255, 255, 255, 0.05);
                color: #E8D5B5;
                border: 1px solid rgba(232, 213, 181, 0.16);
            }

            .admin-nav-link.is-active .admin-nav-badge {
                background: rgba(12, 18, 34, 0.12);
                color: #0C1222;
                border-color: rgba(12, 18, 34, 0.08);
            }

            .admin-main-card,
            main .rounded-3xl.border.border-slate-200.bg-white {
                border-color: rgba(15, 23, 42, 0.08) !important;
                box-shadow: 0 18px 50px -34px rgba(7, 11, 20, 0.35);
                backdrop-filter: blur(8px);
            }

            main .bg-blue-700,
            main .bg-blue-600,
            main a.bg-blue-700,
            main button.bg-blue-700 {
                background: linear-gradient(135deg, #1C2740 0%, #0C1222 100%) !important;
                box-shadow: 0 16px 30px -18px rgba(12, 18, 34, 0.7) !important;
            }

            main .hover\:bg-blue-800:hover,
            main a.hover\:bg-blue-800:hover,
            main button.hover\:bg-blue-800:hover {
                background: linear-gradient(135deg, #243352 0%, #141C2E 100%) !important;
            }

            main .text-blue-700 {
                color: #A88754 !important;
            }

            main .focus\:border-blue-500:focus {
                border-color: #C4A574 !important;
            }

            main .focus\:ring-blue-100:focus {
                --tw-ring-color: rgba(196, 165, 116, 0.22) !important;
            }

            .font-display {
                font-family: "Cormorant Garamond", Georgia, serif;
            }

            @keyframes adminFadeUp {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }

            main > * {
                animation: adminFadeUp 420ms ease both;
            }
        </style>
    </head>
    <body class="admin-shell antialiased">
        @php
            $adminUser = auth()->user();
            $canManagePmb = $adminUser?->hasAdminRole('admin_pmb');
            $canManageCrm = $adminUser?->hasAdminRole('admin_pmb', 'operator_crm');
            $navActive = fn (string $pattern): string => request()->routeIs($pattern) ? 'is-active' : '';
        @endphp

        <div class="min-h-screen lg:flex">
            <aside class="admin-sidebar border-b border-white/5 lg:fixed lg:inset-y-0 lg:left-0 lg:flex lg:w-[18.5rem] lg:flex-col lg:border-b-0 lg:border-r lg:border-white/5">
                <div class="flex shrink-0 items-center gap-3 border-b border-white/10 px-6 py-5">
                    @if ($campusSetting->logo_url)
                        <img src="{{ $campusSetting->logo_url }}" alt="{{ $campusSetting->campus_name }}" class="h-12 w-12 rounded-2xl bg-white/95 object-contain p-1 ring-1 ring-[#C4A574]/30">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#E8D5B5] to-[#C4A574] text-sm font-extrabold text-[#0C1222] shadow-[0_0_0_1px_rgba(196,165,116,0.18),0_20px_50px_-30px_rgba(196,165,116,0.45)]">
                            PMB
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate font-display text-xl font-semibold leading-none tracking-wide text-white">{{ $campusSetting->campus_name }}</p>
                        <p class="mt-1.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#D4B896]">Admin Concierge</p>
                    </div>
                </div>

                <nav class="flex gap-2 overflow-x-auto px-3 py-4 lg:min-h-0 lg:flex-1 lg:flex-col lg:space-y-1.5 lg:overflow-y-auto lg:overflow-x-hidden lg:px-3 lg:pb-8">
                    <p class="mb-1 hidden px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-white/35 lg:block">Overview</p>
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ $navActive('admin.dashboard') }}">
                        <span class="admin-nav-badge">DB</span>
                        Dashboard
                    </a>
                    @if ($canManageCrm)
                        <a href="{{ route('admin.ai-dashboard') }}" class="admin-nav-link {{ $navActive('admin.ai-dashboard') }}">
                            <span class="admin-nav-badge">DA</span>
                            Dashboard AI
                        </a>
                    @endif

                    @if ($canManagePmb || $canManageCrm)
                        <p class="mb-1 mt-4 hidden px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-white/35 lg:block">Operasional</p>
                    @endif

                    @if ($canManagePmb)
                        <a href="{{ route('admin.local-applications.index') }}" class="admin-nav-link {{ $navActive('admin.local-applications.*') }}">
                            <span class="admin-nav-badge">PL</span>
                            Pendaftaran Lokal
                        </a>
                    @endif
                    @if ($canManageCrm)
                        <a href="{{ route('admin.ai-chat-leads.index') }}" class="admin-nav-link {{ $navActive('admin.ai-chat-leads.*') }}">
                            <span class="admin-nav-badge">AI</span>
                            CRM AI
                        </a>
                    @endif
                    @if ($canManagePmb)
                        <a href="{{ route('admin.master-pmb.index', 'campuses') }}" class="admin-nav-link {{ $navActive('admin.master-pmb.*') }}">
                            <span class="admin-nav-badge">MP</span>
                            Master PMB
                        </a>
                        <a href="{{ route('admin.pmb-catalog.opened-registrations') }}" class="admin-nav-link {{ $navActive('admin.pmb-catalog.opened-registrations') }}">
                            <span class="admin-nav-badge">PD</span>
                            Pendaftaran Dibuka
                        </a>
                        <a href="{{ route('admin.pmb-catalog.applicants') }}" class="admin-nav-link {{ $navActive('admin.pmb-catalog.applicants') }}">
                            <span class="admin-nav-badge">PN</span>
                            Pendaftar
                        </a>
                        <a href="{{ route('admin.pmb-catalog.study-programs') }}" class="admin-nav-link {{ $navActive('admin.pmb-catalog.study-programs') }}">
                            <span class="admin-nav-badge">PS</span>
                            Program Studi
                        </a>
                        <a href="{{ route('admin.pmb-catalog.periods') }}" class="admin-nav-link {{ $navActive('admin.pmb-catalog.periods') }}">
                            <span class="admin-nav-badge">PR</span>
                            Periode
                        </a>

                        <p class="mb-1 mt-4 hidden px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-white/35 lg:block">Konten & Sistem</p>
                        <a href="{{ route('admin.pmb-information.index') }}" class="admin-nav-link {{ $navActive('admin.pmb-information.*') }}">
                            <span class="admin-nav-badge">KP</span>
                            Konten PMB
                        </a>
                        <a href="{{ route('admin.pmb-cbt.index') }}" class="admin-nav-link {{ $navActive('admin.pmb-cbt.*') }}">
                            <span class="admin-nav-badge">CT</span>
                            Kelola CBT
                        </a>
                        <a href="{{ route('admin.master-pmb.index', 'tuition-fees') }}" class="admin-nav-link {{ $navActive('admin.tuition-fees.*') }}">
                            <span class="admin-nav-badge">BK</span>
                            Biaya Kuliah
                        </a>
                        <a href="{{ route('admin.payment-gateway.edit') }}" class="admin-nav-link {{ $navActive('admin.payment-gateway.*') }}">
                            <span class="admin-nav-badge">DK</span>
                            Pembayaran DOKU
                        </a>
                    @endif
                </nav>

                <div class="hidden border-t border-white/10 px-5 py-4 lg:block">
                    <div class="rounded-2xl border border-[#C4A574]/20 bg-white/[0.03] px-4 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#D4B896]">Signed in</p>
                        <p class="mt-1 truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-white/45">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </aside>

            <div class="min-h-screen flex-1 lg:pl-[18.5rem]">
                <header class="sticky top-0 z-20 border-b border-black/10 bg-[#F4F5F8]/80 backdrop-blur-xl">
                    <div class="flex h-20 items-center justify-between px-5 sm:px-8">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#A88754]">Admin Panel</p>
                            <h1 class="font-display text-[1.85rem] font-semibold leading-none tracking-[-0.02em] text-[#0C1222]">@yield('page_title', 'Dashboard')</h1>
                        </div>

                        <div class="relative" id="admin-user-menu">
                            <button
                                type="button"
                                id="admin-user-menu-button"
                                class="flex items-center gap-3 rounded-2xl border border-black/10 bg-white/70 px-2 py-1.5 shadow-[0_18px_50px_-28px_rgba(7,11,20,0.45)] transition hover:border-[#C4A574]/40 hover:bg-white"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <div class="hidden text-right sm:block">
                                    <p class="text-sm font-semibold text-[#0C1222]">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#141C2E] to-[#070B14] text-sm font-bold text-[#E8D5B5] ring-1 ring-[#C4A574]/30">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <svg class="mr-1 h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                id="admin-user-menu-dropdown"
                                class="absolute right-0 z-30 mt-2 hidden w-60 overflow-hidden rounded-2xl border border-black/10 bg-white/95 py-2 shadow-[0_18px_50px_-28px_rgba(7,11,20,0.45)] backdrop-blur-xl"
                            >
                                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <svg class="h-4 w-4 text-[#A88754]" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074 0z" /></svg>
                                    Edit Profile
                                </a>
                                <a href="{{ route('admin.profile.password.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <svg class="h-4 w-4 text-[#A88754]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                    Edit Password
                                </a>
                                @if ($canManagePmb)
                                    <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                        <svg class="h-4 w-4 text-[#A88754]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>
                                        Setting Website
                                    </a>
                                    <a href="{{ route('admin.payment-gateway.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                        <svg class="h-4 w-4 text-[#A88754]" viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
                                        Pembayaran DOKU
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
                        <div class="mb-6 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-[0_18px_50px_-28px_rgba(7,11,20,0.45)]">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-6 rounded-2xl border border-red-200/80 bg-red-50/90 px-5 py-4 text-sm font-semibold text-red-700 shadow-[0_18px_50px_-28px_rgba(7,11,20,0.45)]">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-200/80 bg-red-50/90 px-5 py-4 text-sm font-semibold text-red-700 shadow-[0_18px_50px_-28px_rgba(7,11,20,0.45)]">
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
