<?php

namespace App\Http\Controllers;

use App\Models\NgopiPagiKonsultasi as K;
use App\Support\NgopiPagiNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Sisi masyarakat (tanpa login). Akses percakapan lewat token acak di URL.
 */
class NgopiPagiPublikController extends Controller
{
    private const SAPAAN = "Selamat pagi, Bapak/Ibu. Terima kasih telah menghubungi layanan Ngopi Pagi – LK3 Dinas Sosial Kota Bogor.\n\n"
        . "Kami siap membantu memberikan informasi, konsultasi dan pendampingan awal terkait permasalahan kesejahteraan sosial dan keluarga. "
        . "Informasi yang Anda sampaikan akan digunakan untuk kepentingan pelayanan dan ditangani secara profesional serta dijaga kerahasiaannya.\n\n"
        . "Petugas kami akan segera menyapa Anda di ruang obrolan ini.\n"
        . "Mari Ngopi Pagi, Ngobrol, Konsultasi, dan Cari Solusi Bersama.";

    public function index(Request $request)
    {
        $lanjut = null;
        if ($token = $request->session()->get('ngopi_token')) {
            $lanjut = K::where('token', $token)->where('tahap', '!=', 'selesai')->first();
            if ($lanjut) {
                // jumlah balasan petugas yang belum dibaca, ditampilkan sebagai notifikasi di tombol "Lanjutkan"
                $lanjut->belum_baca = $lanjut->pesans()->where('pengirim', 'petugas')->whereNull('dibaca_at')->count();
            }
        }

        return view('ngopi-pagi.index', [
            'lanjut' => $lanjut,
            'buka' => $this->sedangBuka(),
        ]);
    }

    public function mulai()
    {
        return view('ngopi-pagi.mulai', ['daftarKecamatan' => K::KECAMATAN]);
    }

    public function store(Request $request)
    {
        // Honeypot: bot biasanya mengisi field ini. Pura-pura sukses.
        if ($request->filled('website')) {
            return redirect()->route('ngopi-pagi.index');
        }

        $d = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kontak' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'kecamatan' => ['required', Rule::in(K::KECAMATAN)],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kebutuhan' => ['required', 'string', 'min:10', 'max:2000'],
            'butuh_segera' => ['required', Rule::in(['0', '1'])],
            'persetujuan' => ['accepted'],
        ], [
            'nama.required' => 'Nama atau inisial wajib diisi.',
            'kontak.required' => 'Nomor yang dapat dihubungi wajib diisi.',
            'kontak.regex' => 'Format nomor tidak valid.',
            'kecamatan.required' => 'Pilih kecamatan.',
            'kecamatan.in' => 'Pilih kecamatan dari daftar.',
            'kelurahan.required' => 'Kelurahan wajib diisi.',
            'kebutuhan.required' => 'Ceritakan singkat permasalahan Anda.',
            'kebutuhan.min' => 'Ceritakan sedikit lebih lengkap (minimal 10 karakter).',
            'persetujuan.accepted' => 'Centang persetujuan penggunaan data agar kami dapat melanjutkan.',
        ]);

        $user = Auth::user();
        $segera = $d['butuh_segera'] === '1';

        $k = K::create([
            'no_konsultasi' => K::buatNomor(),
            'token' => Str::random(40),
            'user_id' => ($user && ($user->role->slug ?? null) === 'masyarakat') ? $user->id : null,
            'nama' => $d['nama'],
            'kontak' => $d['kontak'],
            'kecamatan' => $d['kecamatan'],
            'kelurahan' => $d['kelurahan'],
            'kebutuhan' => $d['kebutuhan'],
            'butuh_segera' => $segera,
            'tahap' => 'chat_masuk',
            'terakhir_pesan_at' => now(),
        ]);

        $k->pesans()->create(['pengirim' => 'masyarakat', 'isi' => $d['kebutuhan']]);
        $k->kirimSistem(self::SAPAAN);
        $k->catat('masuk', null, 'chat_masuk', $segera ? 'Pengguna menandai butuh penanganan segera.' : null);

        NgopiPagiNotifier::kePetugas(
            $segera ? 'Konsultasi baru – BUTUH SEGERA' : 'Konsultasi baru Ngopi Pagi',
            "{$k->nama} ({$k->kelurahan}, {$k->kecamatan}) memulai konsultasi.",
            $k->urlAdmin(),
            ['konsultasi_id' => $k->id, 'segera' => $segera],
        );

        $request->session()->put('ngopi_token', $k->token);

        return redirect()->route('ngopi-pagi.chat', $k->token);
    }

    public function chat(Request $request, string $token)
    {
        $k = $this->cari($token);
        $this->tandaiDibaca($k);
        $request->session()->put('ngopi_token', $k->token);

        return view('ngopi-pagi.chat', ['k' => $k->load(['pesans.user', 'petugas', 'konselor'])]);
    }

    /** Polling pesan baru. */
    public function pesan(Request $request, string $token)
    {
        $k = $this->cari($token);
        $baru = $k->pesans()->with('user')->where('id', '>', (int) $request->query('after', 0))->get();
        $k->load(['petugas', 'konselor']);
        $this->tandaiDibaca($k);

        return response()->json([
            'pesan' => $baru->map(fn ($p) => $p->ringkas($k->konselor_id))->values(),
            'tahap' => $k->tahap,
            'pendamping' => $k->pendampingLabel(),
        ]);
    }

    public function kirim(Request $request, string $token)
    {
        $k = $this->cari($token);

        if ($k->tahap === 'selesai') {
            return response()->json(['message' => 'Layanan ini sudah selesai. Silakan mulai konsultasi baru.'], 422);
        }

        $d = $request->validate(['isi' => ['required', 'string', 'max:2000']]);

        // Notifikasi hanya untuk pesan pertama yang belum dibaca (agar lonceng tidak dibanjiri).
        $adaBelumDibaca = $k->pesans()->where('pengirim', 'masyarakat')->whereNull('dibaca_at')->exists();

        $p = $k->pesans()->create(['pengirim' => 'masyarakat', 'isi' => trim($d['isi'])]);
        $k->forceFill(['terakhir_pesan_at' => now()])->save();

        if (! $adaBelumDibaca) {
            NgopiPagiNotifier::kePetugas(
                'Pesan baru – ' . $k->nama,
                Str::limit(trim($d['isi']), 90),
                $k->urlAdmin(),
                ['konsultasi_id' => $k->id],
                $k->konselor_id ?: $k->petugas_id,
            );
        }

        return response()->json(['pesan' => $p->ringkas()]);
    }

    /** Langkah 9: masyarakat mengonfirmasi apakah kebutuhannya sudah terpenuhi. */
    public function konfirmasi(Request $request, string $token)
    {
        $k = $this->cari($token);
        $jawab = $request->validate(['jawaban' => ['required', Rule::in(['ya', 'belum'])]])['jawaban'];

        if ($k->tahap !== 'konfirmasi' || $k->dikonfirmasi_at) {
            return redirect()->route('ngopi-pagi.chat', $k->token);
        }

        if ($jawab === 'ya') {
            $k->update(['dikonfirmasi_at' => now()]);
            $k->catat('konfirmasi_pengguna', 'konfirmasi', 'konfirmasi', 'Pengguna: kebutuhan sudah terpenuhi.');
            $k->kirimSistem('Terima kasih, konfirmasi Anda sudah kami terima. Petugas akan menutup layanan ini.');
            NgopiPagiNotifier::kePetugas('Pengguna sudah mengonfirmasi', "{$k->nama}: kebutuhan terpenuhi. Siap ditutup.", $k->urlAdmin(), ['konsultasi_id' => $k->id], $k->konselor_id ?: $k->petugas_id);
        } else {
            $k->pindahTahap('monitoring', 'belum_terpenuhi', 'Pengguna: kebutuhan belum terpenuhi.');
            $k->kirimSistem('Baik, kami catat bahwa kebutuhan Anda belum terpenuhi. Petugas akan menindaklanjuti kembali. Anda juga bisa menjelaskan kekurangannya di sini.');
            NgopiPagiNotifier::kePetugas('Kebutuhan belum terpenuhi', "{$k->nama} menyatakan kebutuhannya belum terpenuhi. Kasus dikembalikan ke Monitoring.", $k->urlAdmin(), ['konsultasi_id' => $k->id], $k->konselor_id ?: $k->petugas_id);
        }

        return redirect()->route('ngopi-pagi.chat', $k->token);
    }

    // ---------- util ----------
    private function cari(string $token): K
    {
        return K::where('token', $token)->firstOrFail();
    }

    private function tandaiDibaca(K $k): void
    {
        $k->pesans()->whereIn('pengirim', ['petugas', 'sistem'])->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
    }

    private function sedangBuka(): bool
    {
        $now = now(config('ngopi-pagi.timezone'));
        [$bh, $bm] = array_map('intval', explode(':', config('ngopi-pagi.jam_buka')));
        [$th, $tm] = array_map('intval', explode(':', config('ngopi-pagi.jam_tutup')));

        return $now->isWeekday()
            && $now->between($now->copy()->setTime($bh, $bm), $now->copy()->setTime($th, $tm));
    }
}
