<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ajukan DTSEN - SOLID v4 Dinas Sosial Kota Bogor</title>
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
          colors: { primary: "#003b62", secondary: "#136299", "error-container": "#ffdad6", "on-error-container": "#93000a" },
          fontFamily: { sans: ["Plus Jakarta Sans", "sans-serif"] },
        },
      },
    };
  </script>
  <style>
    body { font-family: "Plus Jakarta Sans", sans-serif; background: #eef2f7; }
    .lay-input {
      width: 100%; border: 1.5px solid #e4e9f0; background: #f7f9ff;
      border-radius: 0.6rem; padding: 0.55rem 0.9rem; font-size: 13.5px; color: #121d26;
    }
    .lay-input:focus { outline: none; border-color: #136299; background: #fff; box-shadow: 0 0 0 3px rgba(19,98,153,0.12); }
    .lay-label { display:block; font-size: 12.5px; font-weight: 600; color: #121d26; margin-bottom: 0.3rem; }
    .lay-error { margin-top: 0.3rem; font-size: 11.5px; font-weight: 600; color: #ba1a1a; }
  </style>
</head>
<body>

  <nav class="bg-primary text-white px-6 py-3 flex items-center justify-between shadow">
    <div class="flex items-center gap-2">
      <img src="{{ asset('assets/img/logo/bogor.png') }}" alt="Logo" class="w-7 h-7 object-contain" />
      <span class="text-sm font-semibold">Dinas Sosial Kota Bogor</span>
    </div>
    <div class="flex items-center gap-4 text-sm">
      <a href="{{ route('dashboard') }}" class="hover:underline flex items-center gap-1">
        <span class="material-symbols-outlined text-[18px]">dashboard</span> Dashboard
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="hover:underline flex items-center gap-1">
          <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
        </button>
      </form>
    </div>
  </nav>

  <main class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">

      <div class="flex items-center justify-between mb-1">
        <h1 class="text-lg font-bold text-[#121d26]">Ajukan Pencetakan Kartu DTSEN</h1>
        <a href="{{ route('layanan.dtsen.riwayat') }}" class="text-secondary text-sm font-semibold hover:underline">
          Lihat Riwayat Pengajuan &rarr;
        </a>
      </div>
      <p class="text-sm text-gray-500 mb-6">
        Isi data sesuai KTP/KK. Lampiran wajib berupa file JPG/PNG/PDF, maksimal 2 MB.
      </p>

      @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-error-container text-on-error-container text-sm px-4 py-3">
          Periksa kembali kolom yang ditandai merah di bawah.
        </div>
      @endif

      <form method="POST" action="{{ route('layanan.dtsen.ajukan.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="grid md:grid-cols-2 gap-5">
          <div>
            <label class="lay-label">NIK</label>
            <input type="text" name="nik" maxlength="20" value="{{ old('nik') }}" class="lay-input" />
            @error('nik') <p class="lay-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="lay-label">Nama Lengkap</label>
            <input type="text" name="nama" value="{{ old('nama') }}" class="lay-input" />
            @error('nama') <p class="lay-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="lay-label">No. KK</label>
            <input type="text" name="no_kk" maxlength="20" value="{{ old('no_kk') }}" class="lay-input" />
            @error('no_kk') <p class="lay-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="lay-label">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="lay-input" />
            @error('tanggal_lahir') <p class="lay-error">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-2">
            <label class="lay-label">Alamat</label>
            <textarea name="alamat" rows="2" class="lay-input">{{ old('alamat') }}</textarea>
            @error('alamat') <p class="lay-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="lay-label">Kelurahan</label>
            <input type="text" name="kelurahan" value="{{ old('kelurahan') }}" class="lay-input" />
            @error('kelurahan') <p class="lay-error">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="lay-label">Alasan Cetak</label>
            <select name="alasan_cetak" class="lay-input">
              <option value="">-Pilih-</option>
              @foreach ([
                'Kartu DTSEN rusak',
                'Kartu DTSEN hilang',
                'Perubahan data anggota keluarga',
                'Pencetakan ulang karena data tidak sesuai',
                'Kartu DTSEN sudah habis masa berlaku',
                'Pengajuan baru untuk keluarga penerima manfaat',
              ] as $opt)
                <option value="{{ $opt }}" @selected(old('alasan_cetak')==$opt)>{{ $opt }}</option>
              @endforeach
            </select>
            @error('alasan_cetak') <p class="lay-error">{{ $message }}</p> @enderror
          </div>
        </div>

        <hr class="border-gray-200" />
        <h2 class="text-sm font-bold text-secondary uppercase">Lampiran</h2>

        <div class="grid md:grid-cols-2 gap-5">
          <div>
            <label class="lay-label">Scan KTP</label>
            <input type="file" name="scan_ktp" accept=".jpg,.jpeg,.png,.pdf" class="lay-input" />
            @error('scan_ktp') <p class="lay-error">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="lay-label">Screenshot DTSEN (existing/lama, jika ada)</label>
            <input type="file" name="screenshot_dtsen" accept=".jpg,.jpeg,.png,.pdf" class="lay-input" />
            @error('screenshot_dtsen') <p class="lay-error">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="pt-2">
          <button type="submit" class="bg-primary text-white font-bold text-sm rounded-full px-6 py-2.5 hover:bg-secondary transition">
            Kirim Pengajuan
          </button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>