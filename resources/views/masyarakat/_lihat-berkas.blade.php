{{-- Pop-up pratinjau berkas. Pakai di halaman mana pun: @include('masyarakat._lihat-berkas')
     Tautan yang memakai pop-up: <a href="URL" data-lihat="gambar|pdf" data-judul="Nama berkas">Lihat</a>
     Kalau JavaScript tidak jalan, tautan tetap membuka berkas di tab baru. --}}
<div id="modal-lihat" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/70 p-3 sm:p-6"
     role="dialog" aria-modal="true" aria-label="Pratinjau berkas">
    <div class="relative flex max-h-full w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between gap-3 border-b border-outline-variant/40 px-4 py-3">
            <p id="modal-lihat-judul" class="min-w-0 truncate text-sm font-semibold text-on-surface"></p>
            <button type="button" id="modal-lihat-tutup" aria-label="Tutup"
                    class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div id="modal-lihat-isi" class="flex min-h-0 flex-1 items-center justify-center overflow-auto bg-surface-container p-2"></div>

        <div class="border-t border-outline-variant/40 px-4 py-2 text-right">
            <a id="modal-lihat-tab" href="#" target="_blank" rel="noopener" class="text-xs font-semibold text-primary underline">Buka di tab baru</a>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__lihatBerkas) return;
        window.__lihatBerkas = true;

        var modal = document.getElementById('modal-lihat');
        var isi = document.getElementById('modal-lihat-isi');
        var judul = document.getElementById('modal-lihat-judul');
        var tab = document.getElementById('modal-lihat-tab');
        var tutup = document.getElementById('modal-lihat-tutup');
        var mobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

        function buka(url, tipe, nama) {
            isi.textContent = '';
            judul.textContent = nama;
            tab.href = url;

            if (tipe === 'pdf' && mobile) {
                // Peramban HP umumnya tidak bisa menampilkan PDF di dalam pop-up
                var kotak = document.createElement('div');
                kotak.className = 'p-6 text-center text-sm text-on-surface-variant';
                kotak.textContent = 'Pratinjau PDF tidak tersedia di HP. ';
                var tombol = document.createElement('a');
                tombol.href = url;
                tombol.target = '_blank';
                tombol.rel = 'noopener';
                tombol.className = 'mt-3 inline-flex rounded-full bg-primary px-4 py-2 text-xs font-semibold text-white';
                tombol.textContent = 'Buka PDF';
                kotak.appendChild(document.createElement('br'));
                kotak.appendChild(tombol);
                isi.appendChild(kotak);
            } else if (tipe === 'pdf') {
                var frame = document.createElement('iframe');
                frame.src = url;
                frame.title = nama;
                frame.className = 'h-[75vh] w-full rounded-lg bg-white';
                isi.appendChild(frame);
            } else {
                var img = document.createElement('img');
                img.src = url;
                img.alt = nama;
                img.className = 'max-h-[75vh] w-auto max-w-full object-contain';
                isi.appendChild(img);
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function tutupModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            isi.textContent = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[data-lihat]') : null;
            if (!a) return;
            // Ctrl / Cmd / Shift + klik tetap membuka tab baru seperti biasa
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;
            e.preventDefault();
            buka(a.href, a.getAttribute('data-lihat'), a.getAttribute('data-judul') || 'Berkas');
        });

        tutup.addEventListener('click', tutupModal);
        modal.addEventListener('click', function (e) { if (e.target === modal) tutupModal(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutupModal();
        });
    })();
</script>
