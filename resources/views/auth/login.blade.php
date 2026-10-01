<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Management System | PT Artha Buana Primacoral</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#096256',
                            'primary-hover': '#074e44',
                            navy: '#2A3956',
                            'navy-dark': '#1e293b',
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50/20 to-slate-100 flex items-center justify-center p-4">

    <div class="max-w-md w-full" x-data="{ showPassword: false }">

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/70 border border-slate-100 overflow-hidden">

            <!-- Header with Logo -->
            <div class="pt-8 pb-6 px-8 text-center bg-gradient-to-b from-teal-50/40 to-transparent">
                <div class="inline-flex items-center justify-center p-3 bg-white rounded-2xl shadow-sm border border-slate-100 mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="PT Artha Buana Primacoral" class="h-16 object-contain">
                </div>
                <h1 class="text-xl font-extrabold text-brand-navy tracking-tight">Employee Management System</h1>
                <p class="text-xs text-brand-primary font-bold mt-1 tracking-wide uppercase">PT Artha Buana Primacoral</p>
                <p class="text-xs text-slate-400 mt-2">Silakan login untuk mengakses data dan dashboard</p>
            </div>

            <!-- Form -->
            <div class="px-8 pb-8 pt-2">

                @if($errors->any())
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-700 text-xs">
                    <i class="fa-solid fa-triangle-exclamation text-base flex-shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                @if(session('info'))
                <div class="mb-5 p-3.5 bg-sky-50 border border-sky-200 rounded-2xl flex items-center gap-3 text-sky-700 text-xs">
                    <i class="fa-solid fa-circle-info text-base flex-shrink-0"></i>
                    <span>{{ session('info') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf

                    <!-- Email / Username -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email / Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-regular fa-envelope text-sm"></i>
                            </span>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   placeholder="nama@primacoral.com"
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition text-slate-800 placeholder:text-slate-400">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700">Password</label>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-11 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition text-slate-800 placeholder:text-slate-400">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-primary focus:ring-brand-primary/30 border-slate-300">
                            <span class="text-xs font-medium text-slate-600">Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full py-3.5 px-4 bg-brand-primary hover:bg-brand-primary-hover text-white text-sm font-bold rounded-xl shadow-lg shadow-teal-900/25 transition duration-200 flex items-center justify-center gap-2 mt-2">
                        <span>Masuk ke Sistem</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <!-- Demo Credentials Helper -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Akun Akses Cepat</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button"
                                onclick="document.getElementById('email').value='superadmin@primacoral.com'; document.getElementById('password').value='password123';"
                                class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-500 bg-slate-50/70 hover:bg-teal-50/50 transition text-left">
                            <span class="block font-bold text-teal-800 text-[11px]">Super Admin</span>
                            <span class="block text-[10px] text-slate-500">superadmin@...</span>
                        </button>
                        <button type="button"
                                onclick="document.getElementById('email').value='admin@primacoral.com'; document.getElementById('password').value='password123';"
                                class="p-2.5 rounded-xl border border-slate-200 hover:border-teal-500 bg-slate-50/70 hover:bg-teal-50/50 transition text-left">
                            <span class="block font-bold text-slate-800 text-[11px]">Admin HRD</span>
                            <span class="block text-[10px] text-slate-500">admin@...</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer Note -->
        <div class="text-center mt-6">
            <p class="text-xs text-slate-400">&copy; {{ date('Y') }} PT Artha Buana Primacoral. All rights reserved.</p>
        </div>

    </div>

</body>
</html>
