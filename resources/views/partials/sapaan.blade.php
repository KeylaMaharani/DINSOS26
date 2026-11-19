@php
    $user = auth()->user();
    $role = $user->role ?? null;

    // Salam sesuai jam (zona WIB)
    $jam = (int) now('Asia/Jakarta')->format('H');
    $salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

    // Pesan singkat sesuai peran
    $pesanRole = [
        'kelurahan' => 'Pantau dan ajukan layanan untuk warga di wilayah kelurahan Anda.',
        'lurah' => 'Tinjau dan tanda tangani berkas pengajuan dari kelurahan Anda.',
        'operator_dinsos' => 'Verifikasi berkas yang masuk dan teruskan ke tahap berikutnya.',
        'kabid' => 'Periksa berkas yang menunggu persetujuan bidang Anda.',
        'kadis' => 'Berikut ringkasan layanan dan berkas yang menunggu persetujuan Anda.',
        'tagana' => 'Lihat laporan kejadian bencana yang menunggu assessment Anda.',
        'perlinsos' => 'Pantau laporan bantuan bencana dan alur penyalurannya.',
        'pengurus_barang' => 'Periksa permohonan barang dan kondisi stok bantuan.',
        'dinkes' => 'Tinjau pengajuan PBI APBD yang menunggu pemeriksaan diagnosa.',
        'bpjs' => 'Aktivasi kepesertaan PBI APBD yang sudah disetujui.',
    ];

    $slug = $role ? $role->normalizedSlug() : null;
    $pesan = ($role && $role->isSuperAdmin())
        ? 'Anda masuk sebagai Super Admin. Seluruh modul dan data dapat Anda pantau dari sini.'
        : ($pesanRole[$slug] ?? 'Berikut ringkasan data layanan yang dapat Anda akses.');

    $tanggal = now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y');
@endphp

<section
    class="rounded-3xl p-5 sm:p-6 bg-gradient-to-r from-brand to-secondary text-white shadow-[0_8px_24px_rgba(10,92,168,0.25)] relative overflow-hidden">
    <span class="pointer-events-none absolute -right-10 -top-12 w-48 h-48 rounded-full border border-white/15"></span>
    <span class="pointer-events-none absolute right-16 -bottom-16 w-40 h-40 rounded-full border border-white/10"></span>

    <div class="relative flex flex-col sm:flex-row sm:items-center gap-4">
        <div
            class="w-14 h-14 shrink-0 rounded-full bg-white/20 ring-2 ring-white/50 flex items-center justify-center text-xl font-extrabold">
            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-[11px] text-white/75">
                {{ $tanggal }}@isset($konteks) &middot; {{ $konteks }}@endisset
            </p>
            <h2 class="text-lg sm:text-xl font-extrabold leading-tight mt-0.5 truncate">
                {{ $salam }}, {{ $user->name ?? 'Pengguna' }}!
            </h2>
            <p class="text-xs text-white/85 mt-1">{{ $pesan }}</p>
        </div>

        @if ($role)
            <span
                class="self-start sm:self-center shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 ring-1 ring-white/30 text-[11px] font-semibold">
                <span class="material-symbols-outlined text-[16px]">badge</span>
                {{ $role->name }}
            </span>
        @endif
    </div>
</section>