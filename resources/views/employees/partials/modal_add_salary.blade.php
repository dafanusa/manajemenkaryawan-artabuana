<!-- Modal Tambah Riwayat Gaji Baru -->
<div x-show="salaryModalOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="salaryModalOpen = false">

    <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full max-h-[92vh] overflow-y-auto border border-slate-100"
         @click.away="salaryModalOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-start justify-between bg-gradient-to-r from-teal-50/60 to-emerald-50/60 rounded-t-3xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                    <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-brand-navy">Tambah Riwayat Gaji Baru</h3>
                    <p class="text-xs text-slate-500">
                        {{ $employee->full_name }} <span class="font-mono text-slate-400">({{ $employee->employee_id }})</span>
                    </p>
                </div>
            </div>
            <button type="button" @click="salaryModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-white/80 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('employees.salary_histories.store', $employee->id) }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Info Alert -->
            <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-start gap-2.5 text-xs text-emerald-900">
                <i class="fa-solid fa-shield-halved text-emerald-600 mt-0.5 text-sm flex-shrink-0"></i>
                <div>
                    <span class="font-bold block">Riwayat Aman & Terarsip Otomatis</span>
                    Data gaji sebelumnya tidak akan terhapus. Sistem akan otomatis menutup masa berlaku gaji lama dan mencatat gaji baru ini ke dalam riwayat.
                </div>
            </div>

            <!-- Tanggal Mulai Berlaku -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    <i class="fa-regular fa-calendar-check text-emerald-600 mr-1"></i>
                    Tanggal Mulai Berlaku <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       name="start_date"
                       required
                       value="{{ date('Y-m-d') }}"
                       class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-brand-primary bg-white font-medium text-slate-800">
                <p class="text-[11px] text-slate-400 mt-1">Sejak tanggal berapa struktur atau nominal gaji baru ini mulai aktif.</p>
            </div>

            <!-- Grade & Level -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Line Grade <span class="text-red-500">*</span></label>
                    <select name="line_grade" required class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-navy">
                        <option value="">Pilih Grade</option>
                        @foreach($grades ?? ['Grade A', 'Grade B', 'Grade C', 'Grade D', 'Grade E', 'Grade F'] as $grd)
                        <option value="{{ $grd }}" {{ old('line_grade', $employee->line_grade) === $grd ? 'selected' : '' }}>{{ $grd }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Level Jabatan <span class="text-red-500">*</span></label>
                    <select name="salary_level" required class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary bg-white font-bold text-brand-navy">
                        <option value="">Pilih Level</option>
                        @foreach($levels ?? ['Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'] as $lvl)
                        <option value="{{ $lvl }}" {{ old('salary_level', $employee->salary_level) === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Nominal Gaji (Pokok, Harian, Per Jam) -->
            <div class="space-y-3 pt-2">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Gaji Pokok (Rp/Bulan) <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="number"
                               name="basic_salary"
                               id="modal_basic_salary"
                               step="any"
                               min="0"
                               required
                               value="{{ old('basic_salary', (float)($employee->basic_salary ?? 0)) }}"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-brand-navy">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Gaji Harian -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Gaji Harian (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number"
                                   name="daily_rate"
                                   id="modal_daily_rate"
                                   step="any"
                                   min="0"
                                   value="{{ old('daily_rate', (float)($employee->daily_rate ?? 0)) }}"
                                   placeholder="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-slate-800">
                        </div>
                    </div>

                    <!-- Gaji Per Jam -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Gaji Per Jam (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number"
                                   name="hourly_rate"
                                   id="modal_hourly_rate"
                                   step="any"
                                   min="0"
                                   value="{{ old('hourly_rate', (float)($employee->hourly_rate ?? 0)) }}"
                                   placeholder="0"
                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary font-mono font-bold text-slate-800">
                        </div>
                    </div>
                </div>

                <!-- Helper auto calculate button -->
                <div class="flex items-center justify-end">
                    <button type="button"
                            onclick="
                                const basic = parseFloat(document.getElementById('modal_basic_salary').value) || 0;
                                if (basic > 0) {
                                    document.getElementById('modal_daily_rate').value = Math.round(basic / 25);
                                    document.getElementById('modal_hourly_rate').value = Math.round(basic / 173);
                                }
                            "
                            class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 underline flex items-center gap-1">
                        <i class="fa-solid fa-calculator"></i>
                        Hitung Otomatis Harian (/25) & Per Jam (/173)
                    </button>
                </div>
            </div>

            <!-- Keterangan / Alasan Perubahan Gaji -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Perubahan Gaji</label>
                <input type="text"
                       name="notes"
                       placeholder="Contoh: Kenaikan Berkala Tahunan 2026 / Promosi Jabatan"
                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-brand-primary text-slate-800">
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button"
                        @click="salaryModalOpen = false"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold text-xs transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-500/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Gaji Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>

