@extends('layouts.admin')

@section('title', 'Kelola Akses Masyarakat - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kelola Akses Masyarakat')

@section('content')
    <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5">
        <h2 class="text-base font-semibold text-on-surface mb-4">Daftar Pengguna Masyarakat</h2>

        <form method="GET" action="{{ route('akun-masyarakat.index') }}" class="mb-4">
            <label class="w-full sm:w-72 h-9 rounded-lg bg-surface-container-low border border-outline-variant/40 flex items-center gap-2 px-3">
                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">search</span>
                <input type="text" name="search" value="{{ request('search') }}" data-live-search
                    placeholder="Cari nama, NIK, atau no. telp..."
                    class="w-full min-w-0 bg-transparent border-0 outline-none text-xs focus:ring-0" />
            </label>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-on-surface-variant border-b border-outline-variant/40">
                        <th class="py-2 w-12">No</th>
                        <th class="py-2">Nama</th>
                        <th class="py-2">NIK</th>
                        <th class="py-2">No. Telp</th>
                        <th class="py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        @php $pbi = $u->pengajuanPbiApbd->first(); @endphp
                        <tr class="border-b border-outline-variant/20">
                            <td class="py-2">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="py-2">{{ $pbi->nama_kepala_keluarga ?? $u->name }}</td>
                            <td class="py-2 font-mono text-xs">{{ $pbi->nik_kepala_keluarga ?? $u->username }}</td>
                            <td class="py-2">{{ $pbi->no_telp ?? '-' }}</td>
                            <td class="py-2 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <form action="{{ route('akun.impersonate', ['akun' => $u->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-brand text-white hover:opacity-90 transition">
                                            <span class="material-symbols-outlined text-[14px]">login</span>
                                            Masuk sebagai
                                        </button>
                                    </form>
                                    <form action="{{ route('akun.destroy', ['akun' => $u->id]) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus akun masyarakat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition">
                                            <span class="material-symbols-outlined text-[14px]">delete</span>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs text-on-surface-variant">Belum ada pengguna masyarakat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>
@endsection
