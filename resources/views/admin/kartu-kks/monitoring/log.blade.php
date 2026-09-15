@extends('layouts.admin')

@section('title', 'Log Kartu KKS')
@section('page_title', 'Kartu KKS — Log Proses')

@section('content')
<div class="space-y-5">

    <form method="GET" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs text-on-surface-variant mb-1">Cari (Nama / NIK)</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full px-3 py-2 rounded-lg border border-outline-variant/50 text-sm" />
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary hover:bg-secondary text-on-primary text-sm font-medium">
            Cari
        </button>
        <a href="{{ route('kartu-kks.monitoring') }}"
            class="px-4 py-2 rounded-lg bg-surface-container text-on-surface text-sm font-medium hover:bg-surface-container-high">
            &larr; Kembali ke Monitoring
        </a>
    </form>

    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Tanggal Proses</th>
                    <th class="text-left px-4 py-3">NIK</th>
                    <th class="text-left px-4 py-3">Username</th>
                    <th class="text-left px-4 py-3">Role</th>
                    <th class="text-left px-4 py-3">Aktivitas</th>
                    <th class="text-left px-4 py-3">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($logs as $log)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->tanggal_proses->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($log->permohonan)
                                <a href="{{ route('kartu-kks.show', $log->permohonan) }}" class="text-primary hover:underline">
                                    {{ $log->permohonan->nik }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $log->username }}</td>
                        <td class="px-4 py-3">{{ $log->rolename }}</td>
                        <td class="px-4 py-3">{{ $log->taskname }}</td>
                        <td class="px-4 py-3 text-on-surface-variant">{{ $log->catatan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">Belum ada log.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-outline-variant/40">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
