<?php

namespace Database\Seeders;

use App\Models\KesanKategori;
use App\Models\KesanPenerima;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DATA CONTOH UNTUK LOKAL SAJA (meniru foto alur). JANGAN dijalankan di production:
 * akun demo memakai kata sandi yang mudah ditebak.
 *   php artisan db:seed --class=KesanDemoSeeder
 */
class KesanDemoSeeder extends Seeder
{
    public function run(): void
    {
        $k = KesanKategori::pluck('id', 'kode');

        $data = [
            ['rtlh', 'Keluarga Bapak Slamet', 'Bogor Selatan', 'Empang', 'tinggi', 3, 'Keluarga Bapak Slamet (3 jiwa) tinggal di rumah yang kondisinya sudah tidak layak huni. Atap bocor, dinding mulai lapuk dan belum memiliki sanitasi yang memadai.', 'Perbaikan rumah (atap, dinding, sanitasi)', 15000000],
            ['anak-yatim-piatu', 'Ananda Siti Aisyah', 'Bogor Barat', 'Menteng', 'tinggi', 2, 'Anak yatim berusia 11 tahun, tinggal bersama ibu yang bekerja serabutan.', 'Santunan bulanan dan perlengkapan sekolah', 3600000],
            ['dhuafa', 'Ibu Siti Aminah', 'Bogor Utara', 'Tegal Gundil', 'sedang', 1, 'Lansia hidup sendiri, tidak memiliki penghasilan tetap.', 'Bantuan kebutuhan harian dan kesehatan', 4800000],
            ['biaya-pendidikan', 'Rizky Maulana', 'Bogor Selatan', 'Batutulis', 'tinggi', 4, 'Siswa SMP berprestasi, orang tua terdampak PHK.', 'Biaya SPP dan buku satu tahun ajaran', 2400000],
            ['bantuan-pangan', 'Keluarga Ibu Rohimah', 'Tanah Sareal', 'Kedung Badak', 'sedang', 5, 'Keluarga dengan lima jiwa, kepala keluarga sakit menahun.', 'Paket sembako tiga bulan', 1800000],
            ['rtlh', 'Keluarga Bapak Darsono', 'Bogor Timur', 'Baranangsiang', 'sedang', 4, 'Rumah semi permanen dengan lantai tanah dan atap rusak.', 'Perbaikan lantai dan atap', 12000000],
        ];

        foreach ($data as [$kat, $nama, $kec, $kel, $pr, $jiwa, $profil, $butuh, $biaya]) {
            KesanPenerima::firstOrCreate(['nama' => $nama], [
                'kesan_kategori_id' => $k[$kat], 'kecamatan' => $kec, 'kelurahan' => $kel, 'prioritas' => $pr,
                'jumlah_jiwa' => $jiwa, 'profil' => $profil, 'kebutuhan' => $butuh, 'estimasi_biaya' => $biaya,
                'status' => 'tersedia', 'aktif' => true,
            ]);
        }

        $role = Role::where('slug', 'filantropi')->first();
        if ($role) {
            $u = User::where('username', 'filantropi.demo')->first() ?? new User();
            $u->forceFill([
                'name' => 'Yayasan Peduli Bangsa (Demo)', 'username' => 'filantropi.demo',
                'email' => 'filantropi.demo@example.com', 'password' => Hash::make('filantropi123'), 'role_id' => $role->id,
            ])->save();
        }
    }
}
