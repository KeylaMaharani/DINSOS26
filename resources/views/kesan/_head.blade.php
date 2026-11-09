{{-- Bagian <head> bersama untuk layout Filantropi & halaman masuk KESAN (token warna = layout lain di SOLID) --}}
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#003b62">
<link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link crossorigin href="https://fonts.gstatic.com" rel="preconnect" />
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: "#003b62", secondary: "#136299", brand: "#0a5ca8", "brand-soft": "#e8f4fd",
                    "secondary-container": "#82c1fd", "on-secondary-container": "#004e7e",
                    surface: "#f7f9ff", "surface-container-low": "#edf4ff", "surface-container": "#e4effd",
                    "on-surface": "#121d26", "on-surface-variant": "#42474e", "outline-variant": "#c2c7cf",
                    "on-primary": "#ffffff", error: "#ba1a1a", "error-container": "#ffdad6", "on-error-container": "#93000a",
                    success: "#2e7d32", "success-container": "#d0f8d0", "on-success-container": "#0a5e0a",
                    warning: "#b26a00", "warning-container": "#fff1cc", "on-warning-container": "#5c3b00",
                    accent: "#f5820a",
                },
                fontFamily: { sans: ["Plus Jakarta Sans", "ui-sans-serif", "system-ui", "sans-serif"] },
            }
        }
    };
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<style>
    html { -webkit-text-size-adjust: 100%; }
    body { overflow-x: hidden; font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    [x-cloak] { display: none !important; }
    .msym-fill { font-variation-settings: 'FILL' 1; }
    /* Header gaya MyAlumni (sama dengan layout mobile akun masyarakat) */
    .kk-hdr {
        position: sticky; top: 0; z-index: 30;
        background: linear-gradient(135deg, #003b62 0%, #0a5ca8 100%);
        padding: calc(env(safe-area-inset-top, 0px) + 14px) 16px 16px;
        box-shadow: 0 4px 14px rgba(0, 59, 98, 0.25);
    }
    .kk-nav {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 30; background: #fff;
        border-top: 1px solid #e4effd; padding-bottom: env(safe-area-inset-bottom, 0px);
        box-shadow: 0 -4px 14px rgba(0, 59, 98, 0.08);
    }
    .kk-scroll { scrollbar-width: none; -ms-overflow-style: none; }
    .kk-scroll::-webkit-scrollbar { display: none; }
</style>
