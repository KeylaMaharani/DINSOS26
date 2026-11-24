{{-- resources/views/partials/gc-syarat.blade.php
     Syarat tampil per pertanyaan (kolom induk_id + induk_nilai), dipakai form petugas DAN form masyarakat.
     Wrapper pertanyaan harus punya: data-gc-id, data-gc-induk, data-gc-induk-nilai (JSON).
     Pertanyaan yang tidak memenuhi syarat disembunyikan & inputnya dinonaktifkan (tidak ikut terkirim, tidak dihitung wajib). --}}
<script>
(function () {
    function norm(t) { return String(t == null ? '' : t).trim().toLowerCase(); }

    // Nilai jawaban sebuah induk. Induk yang sendiri tersembunyi dianggap tidak menjawab (berantai).
    function nilaiInduk(form, id) {
        var wrap = form.querySelector('[data-gc-id="' + id + '"]');
        if (wrap && wrap.hidden) { return []; }

        var hasil = [];
        form.querySelectorAll('[name="jawaban[' + id + ']"],[name="jawaban[' + id + '][]"]').forEach(function (e) {
            if (e.type === 'checkbox' || e.type === 'radio') {
                if (e.checked) { hasil.push(norm(e.value)); }
            } else if (norm(e.value) !== '') {
                hasil.push(norm(e.value));
            }
        });
        return hasil;
    }

    function terapkan(form) {
        var items = Array.prototype.slice.call(form.querySelectorAll('[data-gc-induk]'));

        for (var pass = 0; pass < 5; pass++) {
            var berubah = false;

            items.forEach(function (w) {
                var syarat = [];
                try { syarat = JSON.parse(w.getAttribute('data-gc-induk-nilai') || '[]').map(norm); } catch (e) { syarat = []; }

                var dipilih = nilaiInduk(form, w.getAttribute('data-gc-induk'));
                var tampil = dipilih.some(function (v) { return syarat.indexOf(v) >= 0; });

                if (tampil === !w.hidden) { return; }
                berubah = true;

                w.hidden = !tampil;
                w.style.display = tampil ? '' : 'none'; // class "sm:flex" mengalahkan atribut hidden

                w.querySelectorAll('input, select, textarea').forEach(function (el) {
                    if (!tampil) {
                        if (!el.disabled) { el.disabled = true; el.setAttribute('data-syarat-off', '1'); }
                    } else if (el.hasAttribute('data-syarat-off')) {
                        el.disabled = false;
                        el.removeAttribute('data-syarat-off');
                    }
                });

                if (!tampil) {
                    w.classList.remove('gc-err-on', 'ring-1', 'ring-error');
                    var m = w.querySelector('.gc-err-msg');
                    if (m) { m.remove(); }
                }
            });

            if (!berubah) { break; }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('gc-form') || document.getElementById('form-lengkapi');
        if (!form) { return; }

        var jalan = function () { terapkan(form); };
        form.addEventListener('change', jalan);
        form.addEventListener('input', jalan);
        jalan();
        setTimeout(jalan, 300); // setelah skrip lain (Ya/Tidak, lembar pilihan) selesai menyiapkan isian
    });
})();
</script>
