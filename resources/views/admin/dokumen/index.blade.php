@extends('layouts.admin')

@section('title', 'Dokumen - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Dokumen')

@section('content')
    <div x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        preview: false,
        previewUrl: '',
        previewTitle: '',
        loading: false,
        bukaPreview(url, judul) {
            this.previewUrl = url;
            this.previewTitle = judul;
            this.loading = true;
            this.preview = true;
        },
        tutupPreview() {
            this.preview = false;
            this.previewUrl = '';
            this.loading = false;
        }
    }" @keydown.escape.window="tutupPreview()">

        <div class="flex items-center justify-between gap-3 mb-4">
            <p class="text-sm text-on-surface-variant">Template dokumen yang dapat diunduh.</p>
            @if ($isSuperAdmin)
                <button type="button" @click="open = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-medium hover:bg-secondary transition-colors">
                    <span class="material-symbols-outlined text-[18px]">upload_file</span>
                    Tambah Dokumen
                </button>
            @endif
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/40 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low text-left text-on-surface-variant">
                    <tr>
                        <th class="px-4 py-3 w-12">No</th>
                        <th class="px-4 py-3">Nama Dokumen</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Ukuran</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dokumens as $d)
                        <tr class="border-t border-outline-variant/30">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ $d->nama }}
                                <div class="text-xs text-on-surface-variant font-normal">{{ $d->file_name }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $d->keterangan ?: '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ number_format($d->file_size / 1024, 0, ',', '.') }} KB
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        @click="bukaPreview('{{ route('dokumen.preview', $d) }}', @js($d->nama))"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container text-on-surface text-xs font-medium hover:bg-surface-container-high transition">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        Preview
                                    </button>
                                    <a href="{{ route('dokumen.unduh', $d) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-brand text-white text-xs font-medium hover:opacity-90 transition">
                                        <span class="material-symbols-outlined text-[16px]">download</span>
                                        Unduh
                                    </a>
                                    @if ($isSuperAdmin)
                                        <form method="POST" action="{{ route('dokumen.destroy', $d) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                data-confirm="Hapus dokumen &quot;{{ $d->nama }}&quot;?"
                                                data-confirm-variant="error" data-confirm-title="Hapus Dokumen"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 text-xs font-medium transition">
                                                <span class="material-symbols-outlined text-[16px]">delete</span>
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">
                                Belum ada dokumen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal Preview (semua role boleh) --}}
        <div x-show="preview" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.outside="tutupPreview()" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="bg-surface-container-lowest w-full max-w-5xl h-[90vh] rounded-xl shadow-lg overflow-hidden flex flex-col">

                <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-outline-variant/40">
                    <h3 class="text-base font-semibold text-on-surface truncate" x-text="previewTitle"></h3>
                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="previewUrl" target="_blank" x-show="previewUrl"
                            class="inline-flex items-center gap-1 text-xs text-on-surface-variant hover:text-on-surface">
                            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                            Tab baru
                        </a>
                        <button type="button" @click="tutupPreview()" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <div class="relative flex-1 bg-surface-container-low">
                    <div x-show="loading"
                        class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-on-surface-variant text-sm">
                        <span class="material-symbols-outlined animate-spin">progress_activity</span>
                        Menyiapkan preview...
                    </div>
                    <iframe x-show="previewUrl" :src="previewUrl" @load="loading = false"
                        class="absolute inset-0 w-full h-full border-0"></iframe>
                </div>
            </div>
        </div>

        {{-- Modal Tambah Dokumen (Super Admin) --}}
        @if ($isSuperAdmin)
            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div @click.outside="open = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="bg-surface-container-lowest w-full max-w-md rounded-xl shadow-lg overflow-hidden">

                    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/40">
                        <h3 class="text-base font-semibold text-on-surface">Tambah Dokumen</h3>
                        <button type="button" @click="open = false" class="text-on-surface-variant">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('dokumen.store') }}" enctype="multipart/form-data"
                        class="p-5 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Dokumen</label>
                            <input name="nama" type="text" value="{{ old('nama') }}" required
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm" />
                            @error('nama')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" rows="3" placeholder="Opsional"
                                class="w-full rounded-lg border border-outline-variant/50 px-3 py-2 text-sm">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">File (maks. 10 MB)</label>
                            <input type="file" name="file" required
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods" class="w-full text-sm" />
                            @error('file')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="open = false"
                                class="px-4 py-2 rounded-lg text-sm border border-outline-variant/50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg text-sm bg-primary text-on-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection