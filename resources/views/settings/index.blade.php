@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page_title', 'Pengaturan Sistem & Profil Perusahaan (Super Admin)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">

        <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-brand-primary p-2 flex items-center justify-center flex-shrink-0 border border-teal-100">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="max-h-full object-contain">
            </div>
            <div>
                <h2 class="text-base font-bold text-brand-navy">Identitas Perusahaan & Pengaturan Aplikasi</h2>
                <p class="text-xs text-slate-400">Pengaturan kop dokumen, informasi instansi, dan parameter sistem</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Nama Perusahaan Resmi</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? 'PT ARTHA BUANA PRIMACORAL') }}" required class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Judul Aplikasi</label>
                    <input type="text" name="app_title" value="{{ old('app_title', $settings['app_title'] ?? 'Employee Management System') }}" required class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1.5">Tagline / Bidang Usaha Perusahaan</label>
                    <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline'] ?? 'General Contractor, Supplier & Mining Services') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Email Resmi HRD</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? 'hrd@primacoral.com') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">No. Telepon / Hotline</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '+62 901 321888') }}" class="w-full p-2.5 rounded-xl border border-slate-200">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1.5">Alamat Kantor / Base Operasional</label>
                    <textarea name="company_address" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200">{{ old('company_address', $settings['company_address'] ?? 'Jl. Cenderawasih No. 88, Timika, Papua Tengah') }}</textarea>
                </div>
            </div>

            <!-- Identity Colors Reference Card -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="block font-bold text-slate-700 mb-2 uppercase text-[10px] tracking-wider">Identitas Visual Sesuai Logo PT ABP:</span>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-[#096256] shadow-sm"></div>
                        <span>Primary Color: #096256 (Hijau Tua ABP)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-[#2A3956] shadow-sm"></div>
                        <span>Secondary Color: #2A3956 (Navy Blue ABP)</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>

    </div>

</div>
@endsection
