<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Template dokumen: semua role ber-modul 'dokumen' boleh melihat, preview & mengunduh.
 * Tambah / hapus hanya Super Admin (dicek di route DAN di sini sebagai lapis kedua).
 */
class DokumenController extends Controller
{
    public function index()
    {
        $dokumens = Dokumen::with('uploader')->latest()->get();
        $isSuperAdmin = $this->isSuperAdmin();

        return view('admin.dokumen.index', compact('dokumens', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        abort_unless($this->isSuperAdmin(), 403);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods', 'max:10240'], // 10 MB
        ], [
            'nama.required' => 'Nama dokumen wajib diisi.',
            'nama.max' => 'Nama dokumen maksimal 150 karakter.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
            'file.required' => 'File wajib dipilih.',
            'file.mimes' => 'Format file harus PDF, Word, Excel, PowerPoint, atau OpenDocument.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.uploaded' => 'File gagal diunggah. Periksa batas upload_max_filesize di php.ini.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('dokumen', Str::uuid() . '.' . $ext, 'local');

        Dokumen::create([
            'nama' => $data['nama'],
            'keterangan' => $data['keterangan'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('dokumen.index')->with('success', 'Dokumen berhasil ditambahkan.');
    }

    /**
     * Tampilkan dokumen sebagai PDF inline (untuk iframe preview).
     * PDF ditampilkan langsung; format lain dikonversi dulu via LibreOffice.
     */
    public function preview(Dokumen $dokumen)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($dokumen->file_path), 404, 'File tidak ditemukan.');

        $ext = strtolower(pathinfo($dokumen->file_path, PATHINFO_EXTENSION));
        $absolute = $ext === 'pdf'
            ? $disk->path($dokumen->file_path)
            : $this->konversiKePdf($dokumen);

        $nama = Str::slug(pathinfo($dokumen->file_name, PATHINFO_FILENAME)) ?: 'dokumen';

        return response()->file($absolute, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nama . '.pdf"',
        ]);
    }

    public function unduh(Dokumen $dokumen)
    {
        abort_unless(Storage::disk('local')->exists($dokumen->file_path), 404, 'File tidak ditemukan.');

        return response()->download(
            Storage::disk('local')->path($dokumen->file_path),
            $dokumen->file_name
        );
    }

    public function destroy(Dokumen $dokumen)
    {
        abort_unless($this->isSuperAdmin(), 403);

        // Hapus file asli sekaligus cache hasil konversi preview (jika ada).
        Storage::disk('local')->delete([
            $dokumen->file_path,
            'dokumen-preview/' . $dokumen->id . '.pdf',
        ]);
        $dokumen->delete();

        return redirect()->route('dokumen.index')->with('success', 'Dokumen dihapus.');
    }

    /**
     * Konversi docx/xlsx/pptx/dst ke PDF via LibreOffice (headless).
     * Hasil di-cache di {disk local}/dokumen-preview/{id}.pdf, jadi konversi
     * hanya terjadi sekali per dokumen.
     */
    private function konversiKePdf(Dokumen $dokumen): string
    {
        $disk = Storage::disk('local');
        $cacheRel = 'dokumen-preview/' . $dokumen->id . '.pdf';

        if ($disk->exists($cacheRel)) {
            return $disk->path($cacheRel);
        }

        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $norm = fn (string $p) => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p);

        $soffice = $norm((string) config('services.libreoffice.path'));

        if (preg_match('/^[A-Za-z]:|^\//', $soffice) && ! is_file($soffice)) {
            report(new \RuntimeException('LibreOffice tidak ditemukan di: ' . $soffice));
            abort(500, 'LibreOffice belum terpasang atau path di .env salah.');
        }

        $disk->makeDirectory('dokumen-preview');
        $tmpRel = 'dokumen-preview/tmp-' . Str::uuid();
        $disk->makeDirectory($tmpRel);
        $tmpDir = $norm($disk->path($tmpRel));
        $sumber = $norm($disk->path($dokumen->file_path));

        $hasil = $tmpDir . DIRECTORY_SEPARATOR
            . pathinfo($dokumen->file_path, PATHINFO_FILENAME) . '.pdf';

        $log = '';

        if ($isWindows) {
            // Persis seperti tes yang terbukti berhasil: shell_exec + soffice.bin, tanpa flag tambahan.
            $bin = preg_replace('/soffice\.exe$/i', 'soffice.bin', $soffice);
            if (! is_file($bin)) {
                $bin = $soffice;
            }

            $cmd = '"' . $bin . '" --headless --convert-to pdf --outdir "' . $tmpDir . '" "' . $sumber . '" 2>&1';
            $log = (string) shell_exec($cmd);
            clearstatcache();
        } else {
            // Linux/production: profil terpisah supaya tidak bentrok dengan user web server.
            $profilDir = storage_path('app/lo_profile');
            if (! is_dir($profilDir)) {
                @mkdir($profilDir, 0775, true);
            }

            $p = new Process([
                $soffice,
                '-env:UserInstallation=file://' . $profilDir,
                '--headless', '--norestore', '--nologo', '--nolockcheck',
                '--convert-to', 'pdf', '--outdir', $tmpDir, $sumber,
            ]);
            $p->setTimeout(180);
            $p->run();
            $log = 'exit=' . var_export($p->getExitCode(), true) . ' err=' . trim($p->getErrorOutput());
        }

        if (! is_file($hasil)) {
            $isi = implode(', ', @scandir($tmpDir) ?: []);
            $disk->deleteDirectory($tmpRel);
            report(new \RuntimeException('Konversi preview gagal: ' . trim($log) . ' | isi_outdir=[' . $isi . ']'));
            abort(500, 'Preview tidak tersedia untuk dokumen ini. Silakan unduh file-nya.');
        }

        rename($hasil, $disk->path($cacheRel));
        $disk->deleteDirectory($tmpRel);

        return $disk->path($cacheRel);
    }

    private function isSuperAdmin(): bool
    {
        return (bool) request()->user()?->role?->isSuperAdmin();
    }
}