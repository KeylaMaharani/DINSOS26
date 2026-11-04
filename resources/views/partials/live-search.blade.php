{{-- Pencarian otomatis: input ber-atribut data-live-search men-submit form-nya
     500 ms setelah berhenti mengetik (tanpa Enter), lalu fokus & kursor dikembalikan. --}}
<script>
    (function () {
        var KEY = "live-search-focus:" + location.pathname;
        document.querySelectorAll("input[data-live-search]").forEach(function (input) {
            var form = input.form, timer = null, last = input.value;
            if (!form) return;
            function kirim() {
                last = input.value;
                try { sessionStorage.setItem(KEY, input.name); } catch (e) {}
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }
            input.addEventListener("input", function () {
                clearTimeout(timer);
                timer = setTimeout(function () { if (input.value !== last) kirim(); }, 500);
            });
            input.addEventListener("keydown", function (e) {
                if (e.key === "Enter") { e.preventDefault(); clearTimeout(timer); kirim(); }
            });
            try {
                if (sessionStorage.getItem(KEY) === input.name) {
                    sessionStorage.removeItem(KEY);
                    input.focus();
                    var n = input.value.length;
                    input.setSelectionRange(n, n);
                }
            } catch (e) {}
        });
    })();
</script>
