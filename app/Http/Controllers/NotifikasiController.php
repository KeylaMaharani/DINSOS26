<?php

namespace App\Http\Controllers;

use App\Models\ImpersonateRequest;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = Notifikasi::where('user_id', $user->id)->latest()->limit(15)->get();

        $izinIds = $items->where('tipe', 'izin_masuk')
            ->map(fn ($n) => data_get($n->data, 'izin_id'))->filter()->unique()->all();

        $izins = ImpersonateRequest::with('admin:id,name')
            ->whereIn('id', $izinIds)
            ->where('target_user_id', $user->id)
            ->get()->keyBy('id');

        return response()->json([
            'unread' => Notifikasi::where('user_id', $user->id)->whereNull('dibaca_at')->count(),
            'items' => $items->map(function ($n) use ($izins) {
                $izin = $n->tipe === 'izin_masuk' ? $izins->get(data_get($n->data, 'izin_id')) : null;
                $st = $izin?->statusEfektif();

                return [
                    'id' => $n->id,
                    'tipe' => $n->tipe,
                    'judul' => $n->judul,
                    'pesan' => $n->pesan,
                    'url' => $n->url,
                    'dibaca' => (bool) $n->dibaca_at,
                    'waktu' => $n->created_at->locale('id')->diffForHumans(),
                    'baca_url' => route('notifikasi.baca', $n),
                    'izin' => $izin ? [
                        'status' => $st,
                        'otp' => $st === 'approved' ? $izin->otp : null,
                        'setujui_url' => route('izin.setujui', $izin),
                        'tolak_url' => route('izin.tolak', $izin),
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function baca(Request $request, Notifikasi $notifikasi): JsonResponse
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403);

        if (! $notifikasi->dibaca_at) {
            $notifikasi->update(['dibaca_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function bacaSemua(Request $request): JsonResponse
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->whereNull('dibaca_at')->update(['dibaca_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
