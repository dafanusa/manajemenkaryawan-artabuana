@extends('layouts.app')

@section('title', 'Activity Log')
@section('page_title', 'Audit & Log Aktivitas Sistem (Super Admin)')

@section('content')
<div class="space-y-6">

    <!-- TOP FILTER & SEARCH -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-100 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-brand-navy">Jejak Audit Aktivitas</h2>
            <p class="text-xs text-slate-400">Rekaman kronologis operasi login, penambahan data, edit, penghapusan, dan ekspor</p>
        </div>

        <form method="GET" action="{{ route('activity_logs.index') }}" class="flex items-center gap-2">
            <select name="action" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 bg-white">
                <option value="">Semua Jenis Aksi</option>
                @foreach($actions as $act)
                <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                @endforeach
            </select>

            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user, deskripsi, IP..." class="py-2 pl-8 pr-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-brand-primary">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-xs text-slate-400"></i>
            </div>
        </form>
    </div>

    <!-- LOGS TABLE -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-3">Aktivitas</th>
                        <th class="py-3.5 px-4">Keterangan / Target</th>
                        <th class="py-3.5 px-3">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                            <span class="font-bold text-slate-700 block">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            <span class="text-[10px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-brand-navy">
                            {{ $log->user_name ?? 'System' }}
                        </td>
                        <td class="py-3 px-3">
                            @php
                                $badgeColor = match($log->action) {
                                    'login' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'logout' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    'tambah_karyawan', 'tambah_user' => 'bg-teal-50 text-brand-primary border-teal-200',
                                    'edit_karyawan', 'edit_user', 'update_settings' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'hapus_karyawan', 'hapus_user' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'export_data' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeColor }}">
                                {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 max-w-md truncate">
                            <span class="text-slate-800">{{ $log->description ?? '-' }}</span>
                            @if($log->target_type)
                            <span class="text-[10px] text-slate-400 block">Target: {{ $log->target_type }} #{{ $log->target_id }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-500 text-[11px]">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400">Belum ada riwayat aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
