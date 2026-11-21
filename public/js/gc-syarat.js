(function () {
    'use strict';
    var sibuk = false;
    var norm = function (s) { return String(s == null ? '' : s).trim().toLowerCase(); };

    function cariForm() { return document.querySelector('form[data-gc-form], #form-lengkapi'); }

    function kontrol(w) {
        return w.querySelectorAll('input[name^="jawaban"], select[name^="jawaban"], textarea[name^="jawaban"]');
    }

    function nilaiInduk(form, id) {
        var hasil = [];
        form.querySelectorAll('[name="jawaban[' + id + ']"], [name="jawaban[' + id + '][]"]').forEach(function (e) {
            if (e.type === 'checkbox' || e.type === 'radio') { if (e.checked) { hasil.push(e.value); } }
            else if (e.type !== 'file' && e.value !== '') { hasil.push(e.value); }
        });
        return hasil.map(norm);
    }

    function hitung(form, anak) {
        var sembunyi = {};
        for (var i = 0; i < 5; i++) {
            var berubah = false;
            anak.forEach(function (w) {
                var id = w.dataset.gcId, induk = w.dataset.gcInduk, syarat = [];
                try { syarat = JSON.parse(w.dataset.gcIndukNilai || '[]').map(norm); } catch (e) { syarat = []; }
                var tampil = true;
                if (form.querySelector('[data-gc-id="' + induk + '"]')) {
                    tampil = !sembunyi[induk] && nilaiInduk(form, induk).some(function (v) { return syarat.indexOf(v) >= 0; });
                }
                if (!tampil && !sembunyi[id]) { sembunyi[id] = true; berubah = true; }
                else if (tampil && sembunyi[id]) { delete sembunyi[id]; berubah = true; }
            });
            if (!berubah) { break; }
        }
        return sembunyi;
    }

    function nomori(form) {
        var n = 0;
        form.querySelectorAll('[data-gc-id]').forEach(function (w) {
            if (w.hidden) { return; }
            var b = w.querySelector('[data-gc-no]');
            if (b) { b.textContent = ++n; }
        });
    }

    function terapkan(form, dalam) {
        var anak = Array.prototype.slice.call(form.querySelectorAll('[data-gc-induk]'));
        if (!anak.length) { return; }

        var sembunyi = hitung(form, anak);
        var notif = [];

        anak.forEach(function (w) {
            var mau = !!sembunyi[w.dataset.gcId];
            if (mau === w.hasAttribute('data-gc-sembunyi')) { return; }

            if (mau) {
                w.setAttribute('data-gc-sembunyi', '');
                w.hidden = true;
                kontrol(w).forEach(function (el) {
                    if (el.dataset.gcAsal === undefined) { el.dataset.gcAsal = el.disabled ? '1' : '0'; }
                    if (el.dataset.gcAsal !== '0') { return; }
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        if (el.checked) { el.checked = false; notif.push(el); }
                    } else if (el.type !== 'file' && el.value !== '') {
                        el.value = ''; notif.push(el);
                    }
                    el.disabled = true;
                });
            } else {
                w.removeAttribute('data-gc-sembunyi');
                w.hidden = false;
                kontrol(w).forEach(function (el) {
                    if (el.dataset.gcAsal !== undefined) {
                        el.disabled = el.dataset.gcAsal === '1';
                        delete el.dataset.gcAsal;
                    }
                });
            }
        });

        if (notif.length && (dalam || 0) < 5) {
            sibuk = true;
            notif.forEach(function (el) { el.dispatchEvent(new Event('change', { bubbles: true })); });
            sibuk = false;
            terapkan(form, (dalam || 0) + 1);
            return;
        }
        nomori(form);
    }

    document.addEventListener('change', function (e) {
        if (sibuk) { return; }
        var f = cariForm();
        if (f && f.contains(e.target)) { terapkan(f); }
    });

    function init() { var f = cariForm(); if (f) { terapkan(f); } }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }

    window.gcSyarat = { terapkan: init };
})();