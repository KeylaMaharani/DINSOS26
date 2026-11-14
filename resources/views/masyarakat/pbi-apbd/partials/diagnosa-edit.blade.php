{{-- Taruh di resources/views/admin/pbi-apbd/partials/diagnosa-edit.blade.php
     Dipakai tahap Operator & Dinkes menggantikan blok Diagnosa lama (yang membaca jawaban Ground Check).
     Variabel: $data (PbiApbd, relasi diagnosaLogs.user sudah dimuat), $canDiagnosa, $tahap
     Sumber data: kolom pbi_apbds.diagnosa (diisi warga) + riwayat pbi_apbd_diagnosa_logs. --}}
<div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden shadow-sm">
    <div class="px-5 py-4 border-b border-outline-variant/40">
        <h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">medical_information</span> Diagnosa
        </h3>
        <p class="text-xs text-on-surface-variant mt-1">Diagnosa diisi oleh masyarakat. Anda dapat mengubahnya bila perlu; setiap perubahan tercatat di riwayat dan Log Proses.</p>
    </div>

    <div class="p-5 space-y-4">
        @if ($canDiagnosa)
            <form method="POST" action="{{ route('pbi-approval.' . $tahap . '.diagnosa', $data) }}" class="space-y-3">
                @csrf @method('PUT')
                <label for="diagnosa" class="block text-xs font-semibold text-on-surface-variant">Diagnosa saat ini</label>
                <textarea id="diagnosa" name="diagnosa" rows="3" maxlength="500" required
                          class="w-full px-4 py-3 rounded-lg border border-outline-variant/60 text-sm">{{ old('diagnosa', $data->diagnosa) }}</textarea>
                @error('diagnosa')<p class="text-xs text-error">{{ $message }}</p>@enderror
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-sm font-medium">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Perubahan Diagnosa
                </button>
            </form>
        @else
            <div class="p-3 bg-surface-container/30 rounded-lg border border-outline-variant/30">
                <p class="text-xs text-on-surface-variant mb-1">Diagnosa</p>
                <p class="text-sm font-semibold">{{ $data->diagnosa ?: 'Belum diisi' }}</p>
            </div>
        @endif

        @if ($data->diagnosaLogs->isNotEmpty())
            <div>
                <p class="text-xs font-semibold text-on-surface-variant mb-2">Riwayat</p>
                <div class="space-y-2 max-h-56 overflow-y-auto">
                    @foreach ($data->diagnosaLogs as $log)
                        <div class="p-3 bg-surface-container/30 rounded-lg border border-outline-variant/30">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <span class="text-xs font-semibold">{{ $log->username }} <span class="text-on-surface-variant font-normal">({{ $log->role_name }})</span></span>
                                <span class="text-[11px] text-on-surface-variant whitespace-nowrap">{{ $log->created_at->format('d M Y - H:i') }}</span>
                            </div>
                            <p class="text-sm">{{ $log->diagnosa }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>