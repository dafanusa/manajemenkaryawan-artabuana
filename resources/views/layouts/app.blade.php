<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Employee Management System | PT Artha Buana Primacoral</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#096256',
                            'primary-hover': '#074e44',
                            'primary-light': '#e6f3f0',
                            navy: '#2A3956',
                            'navy-dark': '#1c283e',
                            'navy-light': '#f0f3f8',
                            bg: '#F8FAFC',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
            color: #2A3956;
        }
        [x-cloak] { display: none !important; }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body x-data="{
    sidebarCollapsed: false,
    mobileSidebarOpen: false,
    confirmModal: {
        open: false,
        title: '',
        message: '',
        actionUrl: '',
        method: 'POST',
        confirmText: 'Ya, Lanjutkan',
        danger: true
    },
    openConfirm(title, message, actionUrl, method = 'POST', confirmText = 'Ya, Hapus', danger = true) {
        this.confirmModal.title = title;
        this.confirmModal.message = message;
        this.confirmModal.actionUrl = actionUrl;
        this.confirmModal.method = method;
        this.confirmModal.confirmText = confirmText;
        this.confirmModal.danger = danger;
        this.confirmModal.open = true;
    },
    filePreviewModal: {
        open: false,
        title: '',
        url: '',
        fileName: '',
        isPdf: false,
        isImage: false
    },
    openFilePreview(title, url, fileName = '') {
        this.filePreviewModal.title = title || 'Pratinjau Dokumen';
        this.filePreviewModal.url = url;
        this.filePreviewModal.fileName = fileName || 'Dokumen';
        const lower = url.toLowerCase();
        this.filePreviewModal.isPdf = lower.includes('.pdf') || lower.endsWith('pdf');
        this.filePreviewModal.isImage = lower.match(/\.(jpg|jpeg|png|webp|gif)($|\?)/i) !== null || (!this.filePreviewModal.isPdf && url.startsWith('blob:'));
        this.filePreviewModal.open = true;
    }
}" class="min-h-screen flex flex-col antialiased">

    <!-- Layout Wrapper -->
    <div class="flex h-screen overflow-hidden">

        <!-- Backdrop for mobile drawer -->
        <div x-show="mobileSidebarOpen"
             x-cloak
             @click="mobileSidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden">
        </div>

        <!-- SIDEBAR -->
        <aside :class="{
                'w-64': !sidebarCollapsed,
                'w-20': sidebarCollapsed,
                '-translate-x-full lg:translate-x-0': !mobileSidebarOpen,
                'translate-x-0': mobileSidebarOpen
               }"
               class="fixed lg:static inset-y-0 left-0 z-50 flex flex-col bg-brand-navy text-white transition-all duration-300 ease-in-out shadow-2xl flex-shrink-0">

            <!-- Logo Section -->
            <div class="h-20 flex items-center justify-between px-4 border-b border-white/10 bg-[#1e2a40]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="ABP Logo" class="h-11 w-11 object-contain flex-shrink-0 bg-white p-1 rounded-xl shadow-md">
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms class="flex flex-col leading-tight">
                        <span class="font-bold text-base tracking-wide text-white">ABP SYSTEM</span>
                        <span class="text-[10px] tracking-wider text-teal-300 uppercase font-semibold">PT Artha Buana Primacoral</span>
                    </div>
                </a>
                <button @click="mobileSidebarOpen = false" class="lg:hidden text-white/70 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto py-5 px-3 space-y-1.5">

                <!-- Category: Main -->
                <div x-show="!sidebarCollapsed" class="px-3 pt-2 pb-1 text-[11px] font-bold text-white/40 tracking-wider uppercase">
                    Menu Utama
                </div>

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-brand-primary text-white shadow-lg shadow-teal-900/40 font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                   :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-lg {{ request()->routeIs('dashboard') ? 'text-teal-200' : 'text-slate-400' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                </a>

                <!-- Category: Karyawan -->
                <div x-show="!sidebarCollapsed" class="px-3 pt-4 pb-1 text-[11px] font-bold text-white/40 tracking-wider uppercase">
                    Manajemen Karyawan
                </div>

                <!-- Data Karyawan (Submenu or direct) -->
                <div x-data="{ open: {{ request()->routeIs('employees.*') && !request()->routeIs('employees.create') ? 'true' : 'false' }} }">
                    <button @click="if(!sidebarCollapsed) { open = !open } else { window.location='{{ route('employees.index') }}' }"
                            class="w-full flex items-center justify-between px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('employees.index') || request()->routeIs('employees.show') ? 'bg-white/10 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                            :title="sidebarCollapsed ? 'Data Karyawan' : ''">
                        <div class="flex items-center gap-3 truncate">
                            <i class="fa-solid fa-users-gear w-5 text-center text-lg {{ request()->routeIs('employees.index') ? 'text-teal-300' : 'text-slate-400' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Data Karyawan</span>
                        </div>
                        <i x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"></i>
                    </button>

                    <!-- Submenu -->
                    <div x-show="open && !sidebarCollapsed" x-collapse class="pl-10 pr-2 pt-1 pb-2 space-y-1">
                        <a href="{{ route('employees.index') }}"
                           class="block py-2 px-3 rounded-lg text-xs {{ !request('status') && request()->routeIs('employees.index') ? 'text-teal-300 font-semibold bg-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            • Semua Karyawan
                        </a>
                        <a href="{{ route('employees.index', ['status' => 'Aktif']) }}"
                           class="block py-2 px-3 rounded-lg text-xs {{ request('status') === 'Aktif' ? 'text-emerald-400 font-semibold bg-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            • Karyawan Aktif
                        </a>
                        <a href="{{ route('employees.index', ['status' => 'Tidak Aktif']) }}"
                           class="block py-2 px-3 rounded-lg text-xs {{ request('status') === 'Tidak Aktif' ? 'text-amber-400 font-semibold bg-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            • Karyawan Tidak Aktif
                        </a>
                    </div>
                </div>

                <!-- Input Data Karyawan -->
                <a href="{{ route('employees.create') }}"
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('employees.create') ? 'bg-brand-primary text-white shadow-lg font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                   :title="sidebarCollapsed ? 'Input Data Karyawan' : ''">
                    <i class="fa-solid fa-user-plus w-5 text-center text-lg {{ request()->routeIs('employees.create') ? 'text-teal-200' : 'text-slate-400' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Input Karyawan</span>
                </a>

                <!-- Master Departemen -->
                <a href="{{ route('departments.index') }}"
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('departments.*') ? 'bg-brand-primary text-white shadow-lg font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                   :title="sidebarCollapsed ? 'Master Departemen' : ''">
                    <i class="fa-solid fa-building-user w-5 text-center text-lg {{ request()->routeIs('departments.*') ? 'text-teal-200' : 'text-slate-400' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Master Departemen</span>
                </a>

                @if(Auth::user()->isSuperAdmin())
                <!-- Category: Super Admin Only -->
                <div x-show="!sidebarCollapsed" class="px-3 pt-4 pb-1 text-[11px] font-bold text-teal-400/70 tracking-wider uppercase flex items-center justify-between">
                    <span>Super Admin</span>
                    <span class="text-[9px] bg-teal-900/80 text-teal-300 px-1.5 py-0.5 rounded border border-teal-500/30">PRO</span>
                </div>

                <!-- User Management -->
                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-brand-primary text-white shadow-lg font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                   :title="sidebarCollapsed ? 'User Management' : ''">
                    <i class="fa-solid fa-users-cog w-5 text-center text-lg {{ request()->routeIs('users.*') ? 'text-teal-200' : 'text-slate-400' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">User Management</span>
                </a>

                <!-- Activity Log -->
                <a href="{{ route('activity_logs.index') }}"
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('activity_logs.*') ? 'bg-brand-primary text-white shadow-lg font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                   :title="sidebarCollapsed ? 'Activity Log' : ''">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center text-lg {{ request()->routeIs('activity_logs.*') ? 'text-teal-200' : 'text-slate-400' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Activity Log</span>
                </a>

                <!-- Pengaturan -->
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('settings.*') ? 'bg-brand-primary text-white shadow-lg font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                   :title="sidebarCollapsed ? 'Pengaturan' : ''">
                    <i class="fa-solid fa-sliders w-5 text-center text-lg {{ request()->routeIs('settings.*') ? 'text-teal-200' : 'text-slate-400' }}"></i>
                    <span x-show="!sidebarCollapsed" class="truncate">Pengaturan</span>
                </a>
                @endif

            </div>

            <!-- Footer: User & Logout -->
            <div class="p-3 border-t border-white/10 bg-[#1c283e]">
                <div class="flex items-center justify-between p-2 rounded-xl bg-white/5">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center font-bold text-sm shadow-md flex-shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div x-show="!sidebarCollapsed" class="truncate">
                            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                            <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded {{ Auth::user()->isSuperAdmin() ? 'bg-teal-500/20 text-teal-300' : 'bg-blue-500/20 text-blue-300' }}">
                                {{ Auth::user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" x-show="!sidebarCollapsed">
                        @csrf
                        <button type="submit" title="Logout" class="text-slate-400 hover:text-red-400 p-2 transition">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- MAIN CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-brand-bg">

            <!-- TOPBAR / HEADER -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between shadow-sm z-30 flex-shrink-0">

                <!-- Left: Sidebar Toggle & Page Title -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Mobile Hamburger -->
                    <button @click="mobileSidebarOpen = true" class="lg:hidden text-slate-600 hover:text-brand-primary p-2 rounded-lg hover:bg-slate-100">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>

                    <!-- Desktop Collapse Toggle -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:block text-slate-500 hover:text-brand-primary p-2 rounded-xl hover:bg-slate-100 transition">
                        <i :class="sidebarCollapsed ? 'fa-solid fa-bars' : 'fa-solid fa-bars-staggered'" class="text-lg"></i>
                    </button>

                    <div class="border-l border-slate-200 pl-3 sm:pl-4 hidden sm:block">
                        <h1 class="text-lg sm:text-xl font-bold text-brand-navy">@yield('page_title', 'Employee Management System')</h1>
                        <p class="text-xs text-slate-400 font-medium">PT Artha Buana Primacoral &bull; {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>

                <!-- Right: Quick Links, Search & User info -->
                <div class="flex items-center gap-2 sm:gap-4">

                    <!-- Quick Add Button -->
                    <a href="{{ route('employees.create') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-semibold rounded-xl shadow-md shadow-brand-primary/20 transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Karyawan</span>
                    </a>

                    <!-- User Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 transition">
                            <div class="w-9 h-9 rounded-xl bg-brand-navy text-white flex items-center justify-center font-bold text-sm shadow">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <span class="block text-xs font-bold text-brand-navy leading-none">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] text-teal-600 font-semibold">{{ Auth::user()->isSuperAdmin() ? 'SUPER ADMIN' : 'ADMIN HRD' }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-1"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">

                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs font-bold text-brand-navy">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 text-[9px] font-bold rounded-full bg-teal-50 text-brand-primary">
                                    {{ Auth::user()->role === 'super_admin' ? 'SUPER ADMIN' : 'ADMIN' }}
                                </span>
                            </div>

                            @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-600 hover:bg-slate-50 hover:text-brand-primary transition">
                                <i class="fa-solid fa-sliders text-slate-400 w-4"></i>
                                <span>Pengaturan Sistem</span>
                            </a>
                            <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-600 hover:bg-slate-50 hover:text-brand-primary transition">
                                <i class="fa-solid fa-users text-slate-400 w-4"></i>
                                <span>Kelola Pengguna</span>
                            </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 transition font-medium">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-red-400 w-4"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </header>

            <!-- FLASH NOTIFICATIONS -->
            <div class="px-4 sm:px-6 pt-4">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 flex items-center justify-between p-4 bg-rose-50 border border-rose-200 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <p class="text-sm font-semibold text-rose-800">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                @endif

                @if(session('info'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 flex items-center justify-between p-4 bg-sky-50 border border-sky-200 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-sky-500 text-white flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-info"></i>
                        </div>
                        <p class="text-sm font-semibold text-sky-800">{{ session('info') }}</p>
                    </div>
                    <button @click="show = false" class="text-sky-500 hover:text-sky-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                @endif
            </div>

            <!-- SCROLLABLE BODY CONTENT -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 pb-12">
                @yield('content')
            </main>

        </div>

    </div>

    <!-- REUSABLE CONFIRMATION MODAL -->
    <div x-show="confirmModal.open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="confirmModal.open = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transform transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            <div class="flex items-center gap-4">
                <div :class="confirmModal.danger ? 'bg-red-50 text-red-500' : 'bg-teal-50 text-brand-primary'" class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl flex-shrink-0">
                    <i :class="confirmModal.danger ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-circle-question'"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-brand-navy" x-text="confirmModal.title"></h3>
                    <p class="text-xs text-slate-500 mt-1" x-text="confirmModal.message"></p>
                </div>
            </div>

            <form :action="confirmModal.actionUrl" method="POST" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                <input type="hidden" name="_method" :value="confirmModal.method">

                <button type="button" @click="confirmModal.open = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" :class="confirmModal.danger ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-brand-primary hover:bg-brand-primary-hover text-white'" class="px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition" x-text="confirmModal.confirmText">
                </button>
            </form>

        </div>
    </div>

    <!-- REUSABLE FILE PREVIEW MODAL -->
    <div x-show="filePreviewModal.open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/75 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="filePreviewModal.open = false"
             class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            <!-- Modal Header -->
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center text-sm flex-shrink-0">
                        <i :class="filePreviewModal.isPdf ? 'fa-solid fa-file-pdf' : 'fa-solid fa-file-image'"></i>
                    </div>
                    <div class="truncate">
                        <h3 class="text-sm font-bold text-brand-navy truncate" x-text="filePreviewModal.title"></h3>
                        <p class="text-[11px] text-slate-400 font-mono truncate" x-text="filePreviewModal.fileName"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <a :href="filePreviewModal.url"
                       target="_blank"
                       class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition inline-flex items-center gap-1.5"
                       title="Buka di tab baru">
                        <span>Tab Baru</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>

                    <a :href="filePreviewModal.url"
                       :download="filePreviewModal.fileName"
                       class="px-3.5 py-1.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center gap-1.5"
                       title="Download file">
                        <i class="fa-solid fa-download text-xs"></i>
                        <span>Download</span>
                    </a>

                    <button type="button" @click="filePreviewModal.open = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Content (Preview Body) -->
            <div class="flex-1 overflow-auto p-4 sm:p-6 bg-slate-100/60 flex items-center justify-center min-h-[300px] max-h-[78vh]">
                <template x-if="filePreviewModal.isPdf">
                    <iframe :src="filePreviewModal.url" class="w-full h-[70vh] rounded-2xl border border-slate-200 shadow-inner bg-white"></iframe>
                </template>

                <template x-if="filePreviewModal.isImage">
                    <div class="flex flex-col items-center justify-center max-h-full">
                        <img :src="filePreviewModal.url" :alt="filePreviewModal.title" class="max-h-[70vh] max-w-full object-contain rounded-2xl shadow-md border border-slate-200 bg-white">
                    </div>
                </template>

                <template x-if="!filePreviewModal.isPdf && !filePreviewModal.isImage">
                    <div class="text-center p-8 bg-white rounded-2xl border border-slate-200 max-w-sm">
                        <div class="w-16 h-16 rounded-2xl bg-teal-50 text-brand-primary mx-auto flex items-center justify-center text-2xl mb-3">
                            <i class="fa-solid fa-file"></i>
                        </div>
                        <h4 class="font-bold text-sm text-brand-navy mb-1" x-text="filePreviewModal.fileName"></h4>
                        <p class="text-xs text-slate-400 mb-4">Format dokumen ini tidak mendukung pratinjau langsung di dalam browser.</p>
                        <a :href="filePreviewModal.url" target="_blank" class="px-4 py-2 bg-brand-primary text-white rounded-xl font-bold text-xs shadow hover:bg-brand-primary-hover inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-download"></i> Unduh / Buka Dokumen
                        </a>
                    </div>
                </template>
            </div>

        </div>
    </div>

    @stack('scripts')
</body>
</html>
