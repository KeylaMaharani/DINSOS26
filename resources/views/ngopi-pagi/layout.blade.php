@extends('layouts.app')

@section('title', 'Ngopi Pagi - Konsultasi LK3 Dinas Sosial Kota Bogor')

{{-- CSS bersama semua halaman portal Ngopi Pagi (gaya mengikuti halaman Kesan) --}}
@push('head')
    <style>
        [id] { scroll-margin-top: 7.5rem; }

        :root { --site-pad-r: var(--site-pad-l); }

        /* Tombol media sosial melayang disembunyikan di halaman Ngopi Pagi */
        .social-fab { display: none !important; }

        /* Cegah isi grid/form melebihi lebar layar */
        .np-page-card, .np-page-card > *, .np-step { min-width: 0; }
        .np-page-card input, .np-page-card select, .np-page-card textarea { max-width: 100%; min-width: 0; }

        .np-hero {
            background:
                linear-gradient(110deg, rgba(0, 40, 69, 0.95) 10%, rgba(0, 59, 98, 0.85) 55%, rgba(19, 98, 153, 0.7) 100%),
                #003b62;
            position: relative;
            overflow: hidden;
        }
        .np-hero::before,
        .np-hero::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .np-hero::before { width: 22rem; height: 22rem; right: -6rem; top: -8rem; }
        .np-hero::after { width: 12rem; height: 12rem; left: 8%; bottom: -4rem; border-color: rgba(255, 255, 255, 0.12); }
        .np-hero-icon {
            position: absolute; right: 4%; top: 50%; transform: translateY(-50%);
            opacity: 0.14; pointer-events: none; line-height: 0;
        }
        .np-hero-icon .material-symbols-outlined { font-size: 260px; color: #fff; font-variation-settings: 'FILL' 1; }
        @media (max-width: 900px) { .np-hero-icon { display: none; } }

        .np-page-card {
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 6px 20px rgba(55, 65, 81, 0.08);
        }
        @media (max-width: 639px) { .np-page-card { background: transparent; border: 0; box-shadow: none; border-radius: 0; padding: 0; } }
        @media (min-width: 768px) { .np-page-card { padding: 2rem; } }

        .np-toc-link {
            position: relative; display: flex; align-items: center; gap: 0.6rem;
            padding: 0.6rem 0.75rem; border-radius: 0.6rem;
            font-size: 13.5px; font-weight: 600; color: #42474e; text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .np-toc-link:hover, .np-toc-link.is-active { background: #edf4ff; color: #003b62; }
        .np-toc-dot {
            width: 0.4rem; height: 0.4rem; border-radius: 9999px; background: #c2c7cf; flex-shrink: 0;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .np-toc-link:hover .np-toc-dot, .np-toc-link.is-active .np-toc-dot { background: #136299; transform: scale(1.3); }

        .np-topic {
            background: #fff; border: 1px solid #d9e3f1; border-radius: 0.9rem;
            padding: 1.4rem 0.75rem; text-align: center; text-decoration: none;
            display: flex; flex-direction: column; align-items: center; gap: 0.65rem;
            box-shadow: 0 4px 14px rgba(0, 59, 98, 0.05);
            transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        }
        .np-topic:hover { box-shadow: 0 10px 22px rgba(0, 59, 98, 0.13); transform: translateY(-4px); border-color: #a0caf9; }
        .np-topic-icon {
            width: 3.5rem; height: 3.5rem; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
        }
        .np-topic-icon .material-symbols-outlined { font-size: 28px; font-variation-settings: 'FILL' 1; }
        .np-topic-title { font-weight: 700; font-size: 14px; color: #003b62; line-height: 1.3; }
        .np-topic-sub { font-size: 11px; color: #42474e; }

        .np-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.85rem 1.5rem; border-radius: 0.6rem; font-weight: 700; font-size: 14px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .np-btn:hover { transform: translateY(-1px); }
        .np-btn-accent { background: #f5820a; color: #fff; }
        .np-btn-accent:hover { background: #cc6900; }

        .np-step {
            background: #fff; border: 1px solid #d9e3f1; border-radius: 0.9rem; padding: 1.1rem;
            box-shadow: 0 4px 14px rgba(0, 59, 98, 0.05);
        }

        @keyframes np-uap { 0% { opacity: 0; transform: translateY(6px) scaleY(.8); } 40% { opacity: .9; } 100% { opacity: 0; transform: translateY(-10px) scaleY(1.15); } }
        .np-uap { animation: np-uap 2.2s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .np-uap { animation: none; } }

        @media (max-width: 1023px) { .np-toc { position: static !important; } }
    </style>
@endpush