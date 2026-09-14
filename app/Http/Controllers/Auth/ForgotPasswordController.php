<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password', [
            'token' => null,
            'email' => old('email', ''),
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('status', 'Jika email terdaftar, tautan reset kata sandi sudah dikirim.');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $namaPengguna = $user->username ?? $user->name ?? 'Admin';

        // Gunakan asset() agar menghasilkan URL publik, bukan path lokal server.
        $logoUrl = asset('assets/img/logo/logo.png');

        $htmlBody = '
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f4f7; padding:32px 0; font-family: Arial, Helvetica, sans-serif;">
            <tr>
                <td align="center">
                    <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden;">
                        <tr>
                            <td align="center" style="padding:32px 32px 8px;">
                                <img src="' . $logoUrl . '" alt="Logo SOLID" width="70" height="70" style="object-fit:contain; margin-bottom:12px; display:block; margin-left:auto; margin-right:auto;">
                                <h2 style="margin:0; font-size:19px; color:#0f172a;">Reset Kata Sandi</h2>
                                <p style="margin:4px 0 0; font-size:13px; color:#64748b;">SOLID Dinas Sosial Kota Bogor</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:16px 32px 8px; font-size:14px; color:#334155; line-height:1.6;">
                                <p style="margin:0 0 12px;">Halo ' . e($namaPengguna) . ',</p>
                                <p style="margin:0 0 20px;">Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Silakan klik tombol di bawah ini untuk membuat kata sandi baru.</p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0 32px 24px;">
                                <a href="' . $resetUrl . '" style="display:inline-block; background-color:#0f2f4f; color:#ffffff; text-decoration:none; font-size:14px; font-weight:bold; padding:12px 28px; border-radius:8px;">Atur Ulang Kata Sandi</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 28px; font-size:12.5px; color:#94a3b8; line-height:1.6;">
                                <p style="margin:0 0 6px;">Tautan ini berlaku selama 60 menit.</p>
                                <p style="margin:0;">Jika Anda tidak meminta reset kata sandi, abaikan saja email ini — kata sandi Anda tidak akan berubah.</p>
                            </td>
                        </tr>
                    </table>
                    <p style="font-size:11.5px; color:#94a3b8; margin-top:16px;">&copy; ' . date('Y') . ' Pemerintah Kota Bogor. All right reserved</p>
                </td>
            </tr>
        </table>';

        Mail::html($htmlBody, function ($message) use ($user) {
            $message->to($user->email)->subject('Permintaan Reset Kata Sandi - SOLID Dinas Sosial Kota Bogor');
        });

        return back()->with('status', 'Jika email terdaftar, tautan reset kata sandi sudah dikirim.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.forgot-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

        if (!$record || !Hash::check($validated['token'], $record->token)) {
            throw ValidationException::withMessages(['email' => 'Tautan reset kata sandi tidak valid.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            throw ValidationException::withMessages(['email' => 'Tautan reset kata sandi sudah kedaluwarsa, silakan minta ulang.']);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages(['email' => 'Akun tidak ditemukan.']);
        }

        $user->forceFill(['password' => Hash::make($validated['password'])])->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah, silakan login.');
    }
}
