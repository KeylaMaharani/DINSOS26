@extends('ngopi-pagi.layout-chat')

@section('title', 'Obrolan ' . $k->no_konsultasi . ' - Ngopi Pagi')

@section('content')
@php
    $K = \App\Models\NgopiPagiKonsultasi::class;
    $langkah = $K::TAHAP[$k->tahap]['publik'];
    $selesai = $k->tahap === 'selesai';
    $perluKonfirmasi = $k->tahap === 'konfirmasi' && ! $k->dikonfirmasi_at;
    $awal = $k->pesans->map(fn ($p) => $p->ringkas($k->konselor_id))->values();
@endphp
<div class="h-[100dvh] flex flex-col">
    {{-- Kepala + progres --}}
    <header class="shrink-0 bg-gradient-to-br from-primary to-brand text-white px-4 pt-[calc(env(safe-area-inset-top,0px)+12px)] pb-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('ngopi-pagi.index') }}" aria-label="Kembali" class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">arrow_back</span></a>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-base font-extrabold">Ngopi Pagi</p>
                <p class="text-[11px] text-white/75 truncate">{{ $k->no_konsultasi }} · LK3 Dinas Sosial Kota Bogor</p>
            </div>
            <button type="button" id="salin" class="px-3 h-9 rounded-full bg-white/15 text-[11px] font-semibold inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">link</span><span id="salin-teks">Simpan tautan</span></button>
        </div>
        <div class="mt-2.5 flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2">
            <span class="material-symbols-outlined text-[18px] text-crema">support_agent</span>
            <p id="pendamping" class="text-xs font-semibold truncate">{{ $k->pendampingLabel() }}</p>
            <button type="button" id="notif-btn" class="hidden ml-auto shrink-0 inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-1 text-[10px] font-bold"><span class="material-symbols-outlined text-[14px]">notifications</span>Aktifkan notifikasi</button>
        </div>
        <ol class="mt-3 grid grid-cols-5 gap-1" aria-label="Status layanan">
            @foreach ($K::PUBLIK as $i => $nama)
                <li class="min-w-0">
                    <div class="h-1.5 rounded-full {{ $i + 1 <= $langkah ? 'bg-crema' : 'bg-white/25' }}"></div>
                    <p class="mt-1 text-[10px] truncate {{ $i + 1 === $langkah ? 'font-bold text-white' : 'text-white/60' }}">{{ $nama }}</p>
                </li>
            @endforeach
        </ol>
    </header>

    {{-- Kartu konfirmasi (langkah 9) --}}
    @if ($perluKonfirmasi)
        <div class="shrink-0 bg-amber-50 border-b border-amber-200 px-4 py-3">
            <p class="text-sm font-bold text-amber-900">Apakah kebutuhan Anda sudah terpenuhi?</p>
            <form method="POST" action="{{ route('ngopi-pagi.chat.konfirmasi', $k->token) }}" class="mt-2 grid grid-cols-2 gap-2">
                @csrf
                <button name="jawaban" value="ya" class="py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold">Ya, sudah</button>
                <button name="jawaban" value="belum" class="py-2.5 rounded-xl bg-white border border-amber-400 text-amber-900 text-sm font-bold">Belum, masih butuh bantuan</button>
            </form>
        </div>
    @elseif ($k->tahap === 'konfirmasi')
        <div class="shrink-0 bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 text-xs font-semibold text-emerald-800">Terima kasih, konfirmasi Anda sudah diterima. Petugas akan menutup layanan.</div>
    @endif

    {{-- Pesan --}}
    <main id="chat" data-poll="{{ route('ngopi-pagi.chat.pesan', $k->token) }}" data-kirim="{{ route('ngopi-pagi.chat.kirim', $k->token) }}" data-tahap="{{ $k->tahap }}"
        class="flex-1 overflow-y-auto px-4 py-4 space-y-2 bg-[#eef4fa]" aria-live="polite"></main>
    <script type="application/json" id="initial">{!! json_encode($awal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

    <button type="button" id="toast" class="hidden fixed left-1/2 -translate-x-1/2 bottom-24 z-20 rounded-full bg-brand text-white text-xs font-bold px-4 py-2 shadow-lg items-center gap-1"><span class="material-symbols-outlined text-[16px]">arrow_downward</span>Pesan baru</button>

    {{-- Kolom tulis --}}
    @if ($selesai)
        <div class="shrink-0 px-4 py-4 pb-[calc(env(safe-area-inset-bottom,0px)+16px)] bg-white border-t border-outline-variant/40 text-center">
            <p class="text-sm font-semibold">Layanan ini sudah selesai.</p>
            <a href="{{ route('ngopi-pagi.mulai') }}" class="mt-2 inline-flex px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-bold">Mulai konsultasi baru</a>
        </div>
    @else
        <form id="form" class="shrink-0 bg-white border-t border-outline-variant/40 px-3 pt-2.5 pb-[calc(env(safe-area-inset-bottom,0px)+10px)] flex items-end gap-2" autocomplete="off">
            @csrf
            <textarea name="isi" rows="1" maxlength="2000" placeholder="Tulis pesan…" aria-label="Tulis pesan"
                class="flex-1 resize-none max-h-32 rounded-2xl border border-outline-variant/70 px-4 py-3 text-[15px] outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"></textarea>
            <button type="submit" aria-label="Kirim" class="w-12 h-12 shrink-0 rounded-full bg-brand text-white flex items-center justify-center disabled:opacity-50"><span class="material-symbols-outlined text-[22px]">send</span></button>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var box = document.getElementById('chat'), form = document.getElementById('form');
    var seen = new Set(), last = 0, tahap = box.dataset.tahap;
    var judulAsli = document.title, belum = 0;
    var toast = document.getElementById('toast');
    var notifBtn = document.getElementById('notif-btn');

    // ---------- notifikasi balasan ----------
    function bip() {
        try {
            var C = window.AudioContext || window.webkitAudioContext; if (!C) return;
            var c = new C(), o = c.createOscillator(), g = c.createGain();
            o.type = 'sine'; o.frequency.value = 880; g.gain.value = 0.05;
            o.connect(g); g.connect(c.destination); o.start();
            setTimeout(function () { o.stop(); c.close(); }, 180);
        } catch (e) {}
    }
    function tampilNotif(p) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            var n = new Notification('Ngopi Pagi – ' + (p.nama || 'Petugas'), { body: p.isi.slice(0, 120), tag: 'ngopi-pagi' });
            n.onclick = function () { window.focus(); n.close(); };
        } catch (e) {}
    }
    function resetJudul() { belum = 0; document.title = judulAsli; }
    function adaBaru(p) {
        if (p.pengirim === 'masyarakat') return;
        if (document.hidden || !document.hasFocus()) {
            belum++; document.title = '(' + belum + ') Balasan baru · Ngopi Pagi';
            bip(); tampilNotif(p);
        } else if (box.scrollHeight - box.scrollTop - box.clientHeight >= 140) {
            toast.classList.remove('hidden'); toast.classList.add('inline-flex');
        }
    }
    window.addEventListener('focus', resetJudul);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) resetJudul(); });
    toast.addEventListener('click', function () { box.scrollTop = box.scrollHeight; toast.classList.add('hidden'); toast.classList.remove('inline-flex'); });
    box.addEventListener('scroll', function () {
        if (box.scrollHeight - box.scrollTop - box.clientHeight < 140) { toast.classList.add('hidden'); toast.classList.remove('inline-flex'); }
    });
    if (notifBtn && 'Notification' in window && Notification.permission === 'default') {
        notifBtn.classList.remove('hidden');
        notifBtn.addEventListener('click', function () {
            Notification.requestPermission().then(function () { notifBtn.classList.add('hidden'); });
        });
    }

    // ---------- gelembung chat ----------
    function bubble(p, paksa) {
        if (seen.has(p.id)) return;
        seen.add(p.id); last = Math.max(last, p.id);
        var dekat = box.scrollHeight - box.scrollTop - box.clientHeight < 140;
        var row = document.createElement('div'), b = document.createElement('div'), t = document.createElement('div');
        b.className = 'whitespace-pre-wrap break-words text-[14px] leading-relaxed';
        b.textContent = p.isi;
        t.className = 'text-[10px] mt-1 opacity-70 text-right'; t.textContent = p.waktu;
        if (p.pengirim === 'sistem') {
            row.className = 'flex justify-center';
            var s = document.createElement('div');
            s.className = 'max-w-[92%] rounded-2xl bg-white border border-outline-variant/40 px-4 py-3 text-on-surface-variant';
            b.className += ' text-[13px]'; s.appendChild(b); s.appendChild(t); row.appendChild(s);
        } else {
            var mine = p.pengirim === 'masyarakat';
            row.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
            var w = document.createElement('div');
            w.className = 'max-w-[82%] px-4 py-2.5 shadow-sm ' + (mine ? 'bg-brand text-white rounded-3xl rounded-br-lg' : 'bg-white border border-outline-variant/40 rounded-3xl rounded-bl-lg');
            if (!mine) {
                var n = document.createElement('p'); n.className = 'text-[11px] font-bold text-cup mb-0.5'; n.textContent = p.nama ? p.nama + ' · ' + p.peran : 'Petugas Ngopi Pagi'; w.appendChild(n);
            }
            w.appendChild(b); w.appendChild(t); row.appendChild(w);
        }
        box.appendChild(row);
        if (dekat || paksa) box.scrollTop = box.scrollHeight;
    }

    JSON.parse(document.getElementById('initial').textContent).forEach(function (p) { bubble(p, true); });

    // ---------- polling: tetap jalan di tab latar (lebih lambat) agar notifikasi tetap masuk ----------
    function poll() {
        fetch(box.dataset.poll + '?after=' + last, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) return;
                d.pesan.forEach(function (p) { var baru = !seen.has(p.id); bubble(p); if (baru) adaBaru(p); });
                var pe = document.getElementById('pendamping'); if (pe && d.pendamping) pe.textContent = d.pendamping;
                if (d.tahap !== tahap) location.reload(); // status berubah: muat ulang agar progres & kartu konfirmasi tampil
            }).catch(function () {})
            .then(function () { setTimeout(poll, document.hidden ? 12000 : 4000); });
    }
    setTimeout(poll, 4000);

    if (form) {
        var ta = form.querySelector('textarea'), btn = form.querySelector('button');
        ta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey && window.matchMedia('(hover: hover)').matches) { e.preventDefault(); form.requestSubmit(); }
        });
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var isi = ta.value.trim(); if (!isi) return;
            btn.disabled = true;
            fetch(box.dataset.kirim, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ isi: isi })
            }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
              .then(function (x) {
                  if (x.ok && x.d.pesan) { bubble(x.d.pesan, true); ta.value = ''; }
                  else { alert((x.d && x.d.message) || 'Pesan gagal terkirim. Coba lagi.'); }
              }).catch(function () { alert('Pesan gagal terkirim. Periksa koneksi Anda.'); })
              .finally(function () { btn.disabled = false; ta.focus(); });
        });
    }

    document.getElementById('salin').addEventListener('click', function () {
        var el = document.getElementById('salin-teks');
        (navigator.clipboard ? navigator.clipboard.writeText(location.href) : Promise.reject()).then(function () {
            el.textContent = 'Tersalin'; setTimeout(function () { el.textContent = 'Simpan tautan'; }, 2000);
        }).catch(function () { prompt('Salin tautan ini untuk melanjutkan percakapan:', location.href); });
    });
})();
</script>
@endpush
