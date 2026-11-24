<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\ApiClientLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiClientController extends Controller
{
    private const SCOPES = [
        'nik' => 'Cari per NIK',
        'kk' => 'Cari per No. KK',
        'index' => 'Daftar / pencarian',
        'statistik' => 'Statistik',
        'wilayah' => 'Daftar wilayah',
        'detail' => 'Data sensitif (alamat, disabilitas, stunting, tempat/tgl lahir)',
    ];

    public function index(Request $request)
    {
        $tab = in_array($request->input('tab'), ['klien', 'log', 'uji'], true) ? $request->input('tab') : 'klien';

        $clients = ApiClient::orderBy('nama')->get();

        $pakaiHariIni = ApiClientLog::where('event', 'akses')
            ->where('created_at', '>=', today())
            ->whereNotNull('api_client_id')
            ->groupBy('api_client_id')
            ->selectRaw('api_client_id, count(*) n')
            ->pluck('n', 'api_client_id');

        $logs = null;
        $ringkasan = null;

        if ($tab === 'log') {
            $logs = ApiClientLog::query()
                ->when($request->filled('klien'), fn ($q) => $q->where('api_client_id', $request->input('klien')))
                ->when($request->filled('event'), fn ($q) => $q->where('event', $request->input('event')))
                ->when(in_array($request->input('status'), ['2', '4', '5'], true), fn ($q) => $q
                    ->whereBetween('status', [(int) $request->input('status') * 100, (int) $request->input('status') * 100 + 99]))
                ->when($request->filled('ip'), fn ($q) => $q->where('ip', 'like', trim($request->input('ip')) . '%'))
                ->when($request->filled('dari'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('dari')))
                ->when($request->filled('sampai'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('sampai')))
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();

            $ringkasan = ApiClientLog::where('created_at', '>=', now()->subDay())
                ->selectRaw("count(*) total,
                             coalesce(sum(event = 'akses' and status < 400), 0) berhasil,
                             coalesce(sum(event <> 'akses'), 0) ditolak,
                             coalesce(sum(status = 429), 0) dibatasi")
                ->first();
        }

        return view('admin.api-klien.index', [
            'tab' => $tab,
            'clients' => $clients,
            'pakaiHariIni' => $pakaiHariIni,
            'logs' => $logs,
            'ringkasan' => $ringkasan,
            'scopes' => self::SCOPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $key = 'dsl_' . Str::random(40);

        $klien = ApiClient::create($data + ['key_hash' => hash('sha256', $key)]);

        return redirect()->route('api-klien.index')
            ->with('success', "Klien {$klien->nama} dibuat.")
            ->with('api_key_baru', ['nama' => $klien->nama, 'key' => $key]);
    }

    public function update(Request $request, ApiClient $klien)
    {
        $klien->update($this->validasi($request));

        return redirect()->route('api-klien.index')->with('success', "Klien {$klien->nama} diperbarui.");
    }

    /** Key lama langsung tidak berlaku. */
    public function regenerate(ApiClient $klien)
    {
        $key = 'dsl_' . Str::random(40);
        $klien->update(['key_hash' => hash('sha256', $key)]);

        return redirect()->route('api-klien.index')
            ->with('success', "Key {$klien->nama} diganti. Key lama sudah tidak berlaku.")
            ->with('api_key_baru', ['nama' => $klien->nama, 'key' => $key]);
    }

    public function toggle(ApiClient $klien)
    {
        $klien->update(['revoked_at' => $klien->revoked_at ? null : now()]);

        return redirect()->route('api-klien.index')
            ->with('success', $klien->revoked_at ? "Akses {$klien->nama} dicabut." : "Akses {$klien->nama} diaktifkan kembali.");
    }

    public function destroy(ApiClient $klien)
    {
        $nama = $klien->nama;
        $klien->delete(); // riwayat log tetap ada (nama klien tersimpan di log)

        return redirect()->route('api-klien.index')->with('success', "Klien {$nama} dihapus.");
    }

    /* ------------------------------------------------------------------ */

    private function validasi(Request $r): array
    {
        $r->validate([
            'nama' => ['required', 'string', 'max:255'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['in:' . implode(',', array_keys(self::SCOPES))],
            'kecamatan_text' => ['nullable', 'string', 'max:2000'],
            'ips_text' => ['nullable', 'string', 'max:2000'],
            'per_page_max' => ['required', 'integer', 'between:1,100'],
            'rate_per_menit' => ['required', 'integer', 'between:1,600'],
            'kuota_harian' => ['required', 'integer', 'between:1,1000000'],
            'expires_at' => ['nullable', 'date'],
        ], [
            'nama.required' => 'Nama instansi wajib diisi.',
            'scopes.required' => 'Pilih minimal satu hak akses endpoint.',
        ]);

        $ips = $this->baris($r->input('ips_text'));
        foreach ($ips as $ip) {
            $valid = filter_var($ip, FILTER_VALIDATE_IP)
                || (preg_match('#^([^/]+)/(\d{1,3})$#', $ip, $m)
                    && filter_var($m[1], FILTER_VALIDATE_IP)
                    && (int) $m[2] <= (str_contains($m[1], ':') ? 128 : 32));

            if (! $valid) {
                throw ValidationException::withMessages(['ips_text' => "IP tidak valid: {$ip}"]);
            }
        }

        $kec = $this->baris($r->input('kecamatan_text'));

        return [
            'nama' => trim($r->input('nama')),
            'scopes' => array_values($r->input('scopes')),
            'kecamatan' => $kec ?: null,   // null = semua wilayah
            'allowed_ips' => $ips ?: null, // null = IP bebas
            'per_page_max' => (int) $r->input('per_page_max'),
            'rate_per_menit' => (int) $r->input('rate_per_menit'),
            'kuota_harian' => (int) $r->input('kuota_harian'),
            'expires_at' => $r->filled('expires_at') ? $r->date('expires_at')->endOfDay() : null,
        ];
    }

    private function baris(?string $teks): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[\r\n,]+/', (string) $teks))
        )));
    }
}