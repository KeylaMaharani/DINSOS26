{{-- Alur KESAN (6 langkah) untuk halaman publik /kesan. Dipakai: @include('kesan._alur') --}}
@php
    $langkah = [
        ['Login Aplikasi', 'login', 'Filantropi masuk ke aplikasi KESAN menggunakan akun yang telah terdaftar.'],
        ['Pilih Jenis Bantuan', 'category', 'Filantropi dapat memilih kategori bantuan yang ingin disalurkan.'],
        ['Lihat Daftar Calon Penerima', 'format_list_bulleted', 'Aplikasi menampilkan data penerima bantuan sesuai kategori yang dipilih. Filantropi dapat melakukan filter berdasarkan wilayah atau prioritas.'],
        ['Lihat Detail Calon Penerima', 'badge', 'Filantropi dapat melihat informasi lengkap tentang kondisi dan jenis bantuan yang dibutuhkan.'],
        ['Konfirmasi Pilihan', 'fact_check', 'Setelah memilih penerima, filantropi akan mendapatkan ringkasan pilihan sebelum melanjutkan ke tahap penyaluran bantuan.'],
        ['Pantau Penyaluran', 'local_shipping', 'Filantropi dapat memantau status bantuan mulai dari verifikasi penyaluran hingga laporan hasil.'],
    ];
@endphp

@push('head')
    <style>
        .kk-alur-card { position: relative; background: #fff; border: 1px solid #d9e3f1; border-radius: 0.9rem; padding: 1.25rem 1.1rem 1.1rem; box-shadow: 0 4px 14px rgba(0, 59, 98, 0.05); }
        .kk-alur-no { width: 2.25rem; height: 2.25rem; border-radius: 9999px; background: #003b62; color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .kk-alur-ico { color: #136299; font-size: 28px; }
    </style>
@endpush

<div id="alur" class="space-y-space-md">
    <div class="space-y-space-2xs">
        <h3 class="font-headline-md text-headline-md text-primary font-bold">Alur Penggunaan KESAN</h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Enam langkah dari masuk aplikasi sampai memantau hasil penyaluran.</p>
    </div>

    <ol class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ($langkah as $i => [$judul, $ikon, $isi])
            <li class="kk-alur-card">
                <div class="flex items-center gap-3">
                    <span class="kk-alur-no">{{ $i + 1 }}</span>
                    <h4 class="font-bold text-primary leading-snug flex-1">{{ $judul }}</h4>
                    <span class="material-symbols-outlined kk-alur-ico">{{ $ikon }}</span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-on-surface-variant">{{ $isi }}</p>
            </li>
        @endforeach
    </ol>

    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('kesan.masuk') }}" class="fp-btn fp-btn-primary">
            <span class="material-symbols-outlined text-[18px]">account_balance</span>Masuk sebagai Filantropi</a>
        <a href="{{ route('admin.login') }}" class="fp-btn" style="background:#fff;color:#136299;border:2px solid #136299;">
            <span class="material-symbols-outlined text-[18px]">badge</span>Masuk sebagai Admin/Petugas</a>
    </div>
</div>
