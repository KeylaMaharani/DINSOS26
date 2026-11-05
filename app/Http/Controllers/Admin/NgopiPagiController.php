<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NgopiPagiKonsultasi as K;
use App\Models\NgopiPagiMonitoring;
use App\Support\NgopiPagiNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NgopiPagiController extends Controller
{
    private const REGEX_KONTAK = '/^[0-9+\-\s()]{6,20}$/';

    /**
     * Halaman per tahap. Tanpa {konsultasi}: daftar kasus (papan kartu).
     * Dengan {konsultasi}: halaman kerja kasus. Chat Masuk memakai tampilan kotak masuk (inbox).
     */
    public function tahap(Request $request)
    {
        $tahap = (string) $request->route('tahap');
        $konsultasi = $request->route('konsultasi');

        abort_unless(isset(K::TAHAP[$tahap]), 404);

        if ($konsultasi !== null && ! $konsultasi instanceof K) {
            $konsultasi = K::findOrFail($konsultasi);
        }

        if ($konsultasi && $konsultasi->tahap !== $tahap) {
            return redirect()->to($konsultasi->urlAdmin());
        }

        $hitung = K::selectRaw('tahap, count(*) as total')->groupBy('tahap')->pluck('total', 'tahap');
        $belumDibaca = fn ($p) => $p->where('pengirim', 'masyarakat')->whereNull('dibaca_at');

        if ($tahap === 'chat_masuk') {
            $daftar = K::withCount(['pesans as belum_dibaca' => $belumDibaca])
                ->where('tahap', 'chat_masuk')->cari($request->query('q'))->prioritas()->limit(100)->get();

            $terpilih = $konsultasi ?? $daftar->first();
            if ($terpilih) {
                $this->muat($terpilih);
                $daftar->each(fn ($x) => $x->id === $terpilih->id ? $x->belum_dibaca = 0 : null);
            }

            return view('admin.ngopi-pagi.inbox', compact('daftar', 'terpilih', 'hitung'));
        }

        if ($konsultasi) {
            $this->muat($konsultasi);

            return view('admin.ngopi-pagi.show', ['k' => $konsultasi, 'aktif' => $tahap, 'hitung' => $hitung]);
        }

        $filter = $request->query('kategori');
        $items = K::with(['petugas', 'konselor'])
            ->withCount(['pesans as belum_dibaca' => $belumDibaca])
            ->where('tahap', $tahap)
            ->cari($request->query('q'))
            ->when(in_array($filter, ['A', 'B', 'C'], true), fn ($q) => $q->where('kategori', $filter))
            ->when($filter === 'segera', fn ($q) => $q->where('butuh_segera', true))
            ->prioritas()->paginate(12)->withQueryString();

        return view('admin.ngopi-pagi.board', [
            'tahap' => $tahap, 'cfg' => K::TAHAP[$tahap], 'items' => $items, 'hitung' => $hitung, 'filter' => $filter,
        ]);
    }

    /** Halaman kasus generik (dari Rekapitulasi / kasus yang sudah selesai). */
    public function kasus(K $konsultasi)
    {
        $this->muat($konsultasi);
        $hitung = K::selectRaw('tahap, count(*) as total')->groupBy('tahap')->pluck('total', 'tahap');

        return view('admin.ngopi-pagi.show', ['k' => $konsultasi, 'aktif' => 'rekap', 'hitung' => $hitung]);
    }

    public function aksi(Request $request, K $konsultasi)
    {
        $aksi = (string) $request->input('aksi');
        $uid = $request->user()->id;
        $dari = $konsultasi->tahap;

        $syarat = [
            'verifikasi' => 'chat_masuk', 'screening' => 'screening', 'ambil' => 'konsultasi', 'asesmen' => 'konsultasi',
            'tindak_lanjut' => 'tindak_lanjut', 'monitor' => 'monitoring', 'ke_konfirmasi' => 'monitoring', 'tutup' => 'konfirmasi',
        ];

        if ($aksi === 'mundur') {
            $prev = $konsultasi->tahapSebelumnya();
            if (! $prev || $dari === 'selesai') {
                return back()->with('error', 'Kasus ini tidak bisa dikembalikan lagi.');
            }
            if ($dari === 'konfirmasi') {
                $konsultasi->update(['dikonfirmasi_at' => null]); // jawaban lama tidak berlaku lagi
            }
            $konsultasi->pindahTahap($prev, 'mundur', 'Dikembalikan ke ' . K::TAHAP[$prev]['label'], $uid);

            return $this->selesaiAksi($konsultasi, 'Kasus dikembalikan ke tahap ' . K::TAHAP[$prev]['label'] . '.');
        }

        if (! isset($syarat[$aksi]) || $syarat[$aksi] !== $dari) {
            return back()->with('error', 'Aksi tidak sesuai dengan tahap kasus saat ini. Muat ulang halaman.');
        }

        switch ($aksi) {
            case 'verifikasi':
                $d = $request->validate([
                    'nama' => ['required', 'string', 'max:100'],
                    'kontak' => ['required', 'string', 'max:20', 'regex:' . self::REGEX_KONTAK],
                    'kecamatan' => ['required', Rule::in(K::KECAMATAN)],
                    'kelurahan' => ['required', 'string', 'max:100'],
                    'identitas_terverifikasi' => ['accepted'],
                    'verifikasi_catatan' => ['nullable', 'string', 'max:500'],
                ], ['identitas_terverifikasi.accepted' => 'Centang dulu bahwa identitas dan kebutuhan sudah diverifikasi.']);
                unset($d['identitas_terverifikasi']);
                $konsultasi->update($d + ['identitas_terverifikasi' => true, 'petugas_id' => $konsultasi->petugas_id ?? $uid]);
                $konsultasi->pindahTahap('screening', 'verifikasi', $d['verifikasi_catatan'] ?? null, $uid);
                $pesan = 'Data terverifikasi. Kasus diteruskan ke Screening.';
                break;

            case 'screening':
                $d = $request->validate([
                    'kategori' => ['required', Rule::in(array_keys(K::KATEGORI))],
                    'jenis_masalah' => ['required', Rule::in(array_keys(K::JENIS_MASALAH))],
                    'klasifikasi_catatan' => ['nullable', 'string', 'max:1000'],
                ], ['kategori.required' => 'Pilih kategori A, B, atau C.', 'jenis_masalah.required' => 'Pilih jenis masalah.']);
                $konsultasi->update($d);
                // Kategori A cukup diberi informasi oleh admin, jadi langsung ke Tindak Lanjut.
                $ke = $d['kategori'] === 'A' ? 'tindak_lanjut' : 'konsultasi';
                $konsultasi->pindahTahap($ke, 'screening', $d['klasifikasi_catatan'] ?? null, $uid);
                if ($ke === 'konsultasi') {
                    $konsultasi->kirimSistem('Percakapan Anda kami teruskan kepada Petugas/Konselor LK3 untuk konsultasi lebih lanjut.');
                }
                if (in_array($d['kategori'], ['B', 'C'], true)) {
                    $c = $d['kategori'] === 'C';
                    NgopiPagiNotifier::kePetugas(
                        $c ? 'Kasus PRIORITAS (Kategori C)' : 'Kasus menunggu konselor LK3',
                        $c ? "{$konsultasi->nama} – segera lakukan asesmen dan koordinasi/rujukan." : "{$konsultasi->nama} diteruskan ke Konsultasi LK3. Silakan ambil kasus ini.",
                        $konsultasi->urlAdmin(), ['konsultasi_id' => $konsultasi->id], null, $uid
                    );
                }
                $pesan = $ke === 'tindak_lanjut' ? 'Kategori A: langsung ke Tindak Lanjut.' : 'Diteruskan ke Konsultasi LK3.';
                break;

            case 'ambil':
                if ($konsultasi->konselor_id && (int) $konsultasi->konselor_id !== (int) $uid) {
                    return back()->with('error', 'Kasus ini sudah diambil konselor lain.');
                }
                $konsultasi->update(['konselor_id' => $uid]);
                $konsultasi->catat('ambil_kasus', $dari, $dari, null, $uid);
                $konsultasi->kirimSistem($request->user()->name . ' (Konselor LK3) kini mendampingi Anda di percakapan ini.');

                return back()->with('success', 'Anda kini menjadi konselor kasus ini. Pengguna akan melihat nama Anda.');

            case 'asesmen':
                $d = $request->validate([
                    'kondisi' => ['required', 'string', 'max:2000'],
                    'kronologi' => ['required', 'string', 'max:2000'],
                    'kebutuhan_asesmen' => ['required', 'string', 'max:2000'],
                    'tingkat_urgensi' => ['required', Rule::in(array_keys(K::URGENSI))],
                    'hasil_asesmen' => ['required', 'string', 'max:2000'],
                ]);
                $konsultasi->update($d + ['konselor_id' => $konsultasi->konselor_id ?? $uid]);
                $konsultasi->pindahTahap('tindak_lanjut', 'asesmen', $d['hasil_asesmen'], $uid);
                $pesan = 'Hasil asesmen tersimpan. Lanjut ke Tindak Lanjut.';
                break;

            case 'tindak_lanjut':
                $d = $request->validate([
                    'jenis_tindak_lanjut' => ['required', Rule::in(array_keys(K::TINDAK_LANJUT))],
                    'rencana_intervensi' => ['required', 'string', 'max:2000'],
                    'tujuan_rujukan' => ['nullable', 'required_if:jenis_tindak_lanjut,rujukan', 'string', 'max:150'],
                    'output_layanan' => ['nullable', 'string', 'max:150'],
                ], ['tujuan_rujukan.required_if' => 'Isi tujuan rujukan (lembaga/layanan yang dituju).']);
                $d['output_layanan'] = ($d['output_layanan'] ?? null) ?: K::TINDAK_LANJUT[$d['jenis_tindak_lanjut']]['output'];
                if ($d['jenis_tindak_lanjut'] !== 'rujukan') {
                    $d['tujuan_rujukan'] = null;
                }
                $konsultasi->update($d + ['petugas_id' => $konsultasi->petugas_id ?? $uid]);
                $konsultasi->pindahTahap('monitoring', 'tindak_lanjut', $d['rencana_intervensi'], $uid);
                $konsultasi->kirimSistem('Layanan untuk Anda: ' . K::TINDAK_LANJUT[$d['jenis_tindak_lanjut']]['label'] . '. Petugas akan terus memantau perkembangannya.');
                $pesan = 'Rencana intervensi tersimpan. Kasus masuk Monitoring.';
                break;

            case 'monitor':
                $d = $request->validate([
                    'status_perkembangan' => ['required', Rule::in(array_keys(K::STATUS_PERKEMBANGAN))],
                    'catatan' => ['required', 'string', 'max:1000'],
                ], ['catatan.required' => 'Catatan pemantauan wajib diisi.']);
                NgopiPagiMonitoring::create($d + ['konsultasi_id' => $konsultasi->id, 'user_id' => $uid]);
                $konsultasi->catat('monitor', $dari, $dari, K::STATUS_PERKEMBANGAN[$d['status_perkembangan']] . ': ' . $d['catatan'], $uid);

                return back()->with('success', 'Catatan pemantauan ditambahkan.');

            case 'ke_konfirmasi':
                $konsultasi->pindahTahap('konfirmasi', 'ke_konfirmasi', null, $uid);
                $konsultasi->kirimSistem('Kami ingin memastikan kebutuhan Anda sudah terpenuhi. Silakan jawab pada kartu konfirmasi di bagian atas percakapan ini.');
                $pesan = 'Menunggu konfirmasi pengguna.';
                break;

            case 'tutup':
                $d = $request->validate([
                    'catatan_penutupan' => ['nullable', 'string', 'max:1000'],
                    'tanpa_konfirmasi' => ['nullable', 'boolean'],
                ]);
                if (! $konsultasi->dikonfirmasi_at && empty($d['tanpa_konfirmasi'])) {
                    return back()->withErrors(['tanpa_konfirmasi' => 'Pengguna belum mengonfirmasi. Centang “Tutup tanpa konfirmasi pengguna” jika tetap ingin menutup.']);
                }
                $konsultasi->update(['closed_at' => now(), 'closed_by' => $uid, 'catatan_penutupan' => $d['catatan_penutupan'] ?? null]);
                $konsultasi->pindahTahap('selesai', 'tutup', $d['catatan_penutupan'] ?? null, $uid);
                $konsultasi->kirimSistem('Layanan dinyatakan selesai. Terima kasih telah menghubungi Ngopi Pagi – LK3 Dinas Sosial Kota Bogor. Jika membutuhkan bantuan lagi, Anda dapat memulai konsultasi baru kapan saja.');
                $pesan = 'Layanan ditutup dan masuk Rekapitulasi.';
                break;
        }

        return $this->selesaiAksi($konsultasi, $pesan);
    }

    public function kirimPesan(Request $request, K $konsultasi)
    {
        if ($konsultasi->tahap === 'selesai') {
            return response()->json(['message' => 'Layanan sudah selesai.'], 422);
        }

        $d = $request->validate(['isi' => ['required', 'string', 'max:2000']]);
        $p = $konsultasi->pesans()->create(['pengirim' => 'petugas', 'user_id' => $request->user()->id, 'isi' => trim($d['isi'])]);
        $konsultasi->update(['terakhir_pesan_at' => now(), 'petugas_id' => $konsultasi->petugas_id ?? $request->user()->id]);

        return response()->json(['pesan' => $p->load('user')->ringkas($konsultasi->konselor_id)]);
    }

    public function pesan(Request $request, K $konsultasi)
    {
        $baru = $konsultasi->pesans()->with('user')->where('id', '>', (int) $request->query('after', 0))->get();
        $this->tandaiDibaca($konsultasi);

        return response()->json(['pesan' => $baru->map(fn ($p) => $p->ringkas($konsultasi->konselor_id))->values(), 'tahap' => $konsultasi->tahap]);
    }

    // ---------- Rekapitulasi (langkah 11) ----------
    public function rekap(Request $request)
    {
        $dasar = $this->rekapQuery($request);

        $stat = [
            'total' => (clone $dasar)->count(),
            'aktif' => (clone $dasar)->where('tahap', '!=', 'selesai')->count(),
            'selesai' => (clone $dasar)->where('tahap', 'selesai')->count(),
            'segera' => (clone $dasar)->where('butuh_segera', true)->count(),
        ];
        $perKategori = (clone $dasar)->selectRaw('kategori, count(*) as total')->groupBy('kategori')->pluck('total', 'kategori');
        $perJenis = (clone $dasar)->whereNotNull('jenis_tindak_lanjut')->selectRaw('jenis_tindak_lanjut, count(*) as total')->groupBy('jenis_tindak_lanjut')->pluck('total', 'jenis_tindak_lanjut');

        $items = (clone $dasar)->with('konselor')->latest()->paginate(20)->withQueryString();
        $hitung = K::selectRaw('tahap, count(*) as total')->groupBy('tahap')->pluck('total', 'tahap');

        return view('admin.ngopi-pagi.rekap', compact('items', 'stat', 'perKategori', 'perJenis', 'hitung'));
    }

    public function export(Request $request)
    {
        $rows = $this->rekapQuery($request)->with('konselor')->latest()->get();

        // Hindari CSV injection: sel yang diawali = + - @ diberi tanda petik.
        $aman = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;

        return response()->streamDownload(function () use ($rows, $aman) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['No Konsultasi', 'Tanggal Masuk', 'Nama', 'Kontak', 'Kecamatan', 'Kelurahan', 'Kategori', 'Jenis Masalah', 'Urgensi', 'Tindak Lanjut', 'Tujuan Rujukan', 'Output Layanan', 'Tahap', 'Konselor', 'Dikonfirmasi', 'Selesai Pada']);
            foreach ($rows as $r) {
                fputcsv($out, array_map($aman, [
                    $r->no_konsultasi, $r->created_at->format('Y-m-d H:i'), $r->nama, $r->kontak, $r->kecamatan, $r->kelurahan,
                    $r->kategori, K::JENIS_MASALAH[$r->jenis_masalah] ?? '', K::URGENSI[$r->tingkat_urgensi] ?? '',
                    K::TINDAK_LANJUT[$r->jenis_tindak_lanjut]['label'] ?? '', $r->tujuan_rujukan, $r->output_layanan,
                    K::TAHAP[$r->tahap]['label'] ?? $r->tahap, $r->konselor->name ?? '',
                    $r->dikonfirmasi_at?->format('Y-m-d H:i'), $r->closed_at?->format('Y-m-d H:i'),
                ]));
            }
            fclose($out);
        }, 'rekap-ngopi-pagi-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------- util ----------
    private function rekapQuery(Request $r)
    {
        return K::query()
            ->cari($r->query('q'))
            ->when($r->filled('tanggal_awal'), fn ($q) => $q->whereDate('created_at', '>=', $r->query('tanggal_awal')))
            ->when($r->filled('tanggal_akhir'), fn ($q) => $q->whereDate('created_at', '<=', $r->query('tanggal_akhir')))
            ->when(isset(K::TAHAP[$r->query('tahap')]), fn ($q) => $q->where('tahap', $r->query('tahap')))
            ->when(isset(K::KATEGORI[$r->query('kategori')]), fn ($q) => $q->where('kategori', $r->query('kategori')))
            ->when(in_array($r->query('kecamatan'), K::KECAMATAN, true), fn ($q) => $q->where('kecamatan', $r->query('kecamatan')));
    }

    private function muat(K $k): void
    {
        $this->tandaiDibaca($k);
        $k->load(['pesans.user', 'monitorings.user', 'logs.user', 'petugas', 'konselor']);
    }

    private function tandaiDibaca(K $k): void
    {
        $k->pesans()->where('pengirim', 'masyarakat')->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
    }

    private function selesaiAksi(K $k, string $pesan)
    {
        return redirect()->to($k->fresh()->urlAdmin())->with('success', $pesan);
    }
}
