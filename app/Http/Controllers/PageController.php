<?php

namespace App\Http\Controllers;

/**
 * Halaman-halaman statis SOLID v4 Dinas Sosial Kota Bogor.
 *
 * Konversi ini bersifat struktural: tampilan dan perilaku JS sisi-klien
 * dipertahankan seperti aslinya. Form login, registrasi, dan cek desil
 * BELUM disambungkan ke database — silakan lengkapi validasi & logika
 * penyimpanan pada method terkait sesuai kebutuhan.
 */
class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function registrasiStep1()
    {
        return view('registrasi.step1');
    }

    public function registrasiStep2()
    {
        return view('registrasi.step2');
    }

    public function cekDensil()
    {
        return view('cek-densil');
    }

    public function kejadianBencana()
    {
        return view('kejadian-bencana');
    }

    public function kesan()
    {
        return view('kesan');
    }

    public function tutorialPendaftaran()
    {
        return view('tutorial-pendaftaran');
    }
}
