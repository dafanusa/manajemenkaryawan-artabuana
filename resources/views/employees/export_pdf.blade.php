<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Karyawan - PT Artha Buana Primacoral</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            @page { size: landscape; margin: 10mm 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 text-slate-800">

    <div class="no-print max-w-7xl mx-auto mb-4 flex items-center justify-between bg-white p-4 rounded-2xl shadow border border-slate-200">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Pratinjau Cetak / PDF Laporan Karyawan</h2>
            <p class="text-xs text-slate-400">Total data tercetak: {{ $employees->count() }} orang</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">Tutup</button>
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#096256] text-white text-xs font-bold shadow hover:bg-[#074e44] flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- REPORT CONTAINER -->
    <div class="max-w-7xl mx-auto bg-white p-8 rounded-2xl shadow border border-slate-200 space-y-4">

        <!-- KOP LAPORAN -->
        <div class="border-b-2 border-[#2A3956] pb-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo ABP" class="h-14 object-contain">
                <div>
                    <h1 class="text-base font-black text-[#2A3956]">PT ARTHA BUANA PRIMACORAL</h1>
                    <p class="text-[10px] text-[#096256] font-bold uppercase tracking-wider">Employee Management System &bull; Human Resources Department</p>
                </div>
            </div>
            <div class="text-right text-[10px] text-slate-500">
                <span class="block font-bold text-xs text-slate-800 uppercase">Laporan Rekapitulasi Data Karyawan</span>
                <span>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</span>
            </div>
        </div>

        <!-- TABLE -->
        <table class="w-full text-left text-[10px] border border-slate-300">
            <thead class="bg-slate-100 font-bold border-b border-slate-300 text-slate-700">
                <tr>
                    <th class="p-2 border-r border-slate-300 text-center w-8">No</th>
                    <th class="p-2 border-r border-slate-300 w-24">ID Karyawan</th>
                    <th class="p-2 border-r border-slate-300">Nama Lengkap</th>
                    <th class="p-2 border-r border-slate-300 w-16 text-center">Gender</th>
                    <th class="p-2 border-r border-slate-300 w-20 text-center">Suku</th>
                    <th class="p-2 border-r border-slate-300 w-16">Agama</th>
                    <th class="p-2 border-r border-slate-300">Jabatan</th>
                    <th class="p-2 border-r border-slate-300">Departemen</th>
                    <th class="p-2 border-r border-slate-300 w-20 text-center">Lokasi Kerja</th>
                    <th class="p-2 border-r border-slate-300 w-16 text-center">Status</th>
                    <th class="p-2 text-center w-20">Tgl Masuk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($employees as $index => $emp)
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }}">
                    <td class="p-1.5 border-r border-slate-200 text-center font-bold">{{ $index + 1 }}</td>
                    <td class="p-1.5 border-r border-slate-200 font-mono font-bold">{{ $emp->employee_id }}</td>
                    <td class="p-1.5 border-r border-slate-200 font-semibold">{{ $emp->full_name }}</td>
                    <td class="p-1.5 border-r border-slate-200 text-center">{{ $emp->gender === 'Laki-laki' ? 'L' : 'P' }}</td>
                    <td class="p-1.5 border-r border-slate-200 text-center font-bold {{ $emp->ethnicity === 'Papua' ? 'text-[#096256]' : 'text-slate-600' }}">{{ $emp->ethnicity }}</td>
                    <td class="p-1.5 border-r border-slate-200">{{ $emp->religion }}</td>
                    <td class="p-1.5 border-r border-slate-200">{{ $emp->position }}</td>
                    <td class="p-1.5 border-r border-slate-200">{{ $emp->department }}</td>
                    <td class="p-1.5 border-r border-slate-200 text-center font-semibold">{{ $emp->work_location }}</td>
                    <td class="p-1.5 border-r border-slate-200 text-center font-bold {{ $emp->employment_status === 'Aktif' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $emp->employment_status }}</td>
                    <td class="p-1.5 text-center">{{ $emp->join_date ? $emp->join_date->format('d/m/Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pt-4 flex items-center justify-between text-[10px] text-slate-400 border-t border-slate-200">
            <span>PT Artha Buana Primacoral &bull; Confidential Corporate Document</span>
            <span>Total Karyawan: {{ $employees->count() }} orang</span>
        </div>

    </div>

</body>
</html>
