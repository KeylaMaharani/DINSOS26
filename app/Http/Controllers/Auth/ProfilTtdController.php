<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/** Tanda tangan tersimpan milik user yang sedang login. Hanya role Lurah dan Kadis. */
class ProfilTtdController extends Controller
{
    /** Role yang boleh menyimpan tanda tangan. */
    private const ROLE_BOLEH = ['lurah', 'kadis'];

    private function pastikanLurah(): void
    {
        abort_unless(
            in_array(Auth::user()->loadMissing('role')->role?->normalizedSlug(), self::ROLE_BOLEH, true),
            403,
            'Fitur tanda tangan hanya untuk akun Lurah dan Kadis.'
        );
    }

    public function store(Request $request)
    {
        $this->pastikanLurah();

        $request->validate([
            'ttd' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'ttd.required' => 'Pilih file tanda tangan terlebih dahulu.',
            'ttd.mimes' => 'Tanda tangan harus berformat PNG atau JPG.',
            'ttd.max' => 'Ukuran tanda tangan maksimal 2 MB.',
        ]);

        $user = Auth::user();
        if ($user->ttd_path) {
            Storage::disk('public')->delete($user->ttd_path);
        }

        $file = $request->file('ttd');
        $path = $file->storeAs('ttd-user', 'u' . $user->id . '-' . now()->timestamp . '.' . strtolower($file->getClientOriginalExtension() ?: $file->extension()), 'public');
        $user->forceFill(['ttd_path' => $path])->save();

        return back()->with('success', 'Tanda tangan tersimpan.');
    }

    public function destroy()
    {
        $this->pastikanLurah();

        $user = Auth::user();
        if ($user->ttd_path) {
            Storage::disk('public')->delete($user->ttd_path);
            $user->forceFill(['ttd_path' => null])->save();
        }

        return back()->with('success', 'Tanda tangan dihapus.');
    }
}
