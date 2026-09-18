<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Pengajuan Kartu KKS</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/logo.png') }}" />
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { primary: "#003b62", secondary: "#136299" } }, fontFamily: { sans: ["Plus Jakarta Sans", "sans-serif"] } },
    };
  </script>
  <style>body { font-family: "Plus Jakarta Sans", sans-serif; background: #eef2f7; }</style>
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

  <main class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">

      <div class="flex items-center justify-between mb-5">
        <h1 class="text-lg font-bold text-[#121d26]">Riwayat Pengajuan Kartu KKS</h1>
        <a href="{{ route('layanan.kartu-kks.ajukan') }}" class="bg-primary text-white text-sm font-bold rounded-full px-5 py-2 hover:bg-secondary transition">
          + Ajukan Baru
        </a>
      </div>

      @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 text-green-800 text-sm px-4 py-3">
          {{ session('success') }}
        </div>
      @endif

      @if ($data->isEmpty())
        <p class="text-sm text-gray-500 text-center py-10">Belum ada pengajuan Kartu KKS.</p>
      @else
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-500 uppercase border-b">
              <tr>
                <th class="py-2 pr-4">Tanggal</th>
                <th class="py-2 pr-4">Nama</th>
                <th class="py-2 pr-4">Masalah Kartu</th>
                <th class="py-2 pr-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach ($data as $item)
                <tr>
                  <td class="py-3 pr-4">{{ $item->tanggal_insert->translatedFormat('d M Y') }}</td>
                  <td class="py-3 pr-4">{{ $item->nama_pemohon }}</td>
                  <td class="py-3 pr-4">{{ ucfirst($item->masalah_kartu) }}</td>
                  <td class="py-3 pr-4">
                    @php $labels = \App\Models\KartuKksPermohonan::statusLabels(); @endphp
                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full
                      @if($item->status === 'selesai') bg-green-100 text-green-700
                      @elseif($item->status === 'ditolak') bg-red-100 text-red-700
                      @else bg-blue-100 text-blue-700 @endif">
                      {{ $labels[$item->status] ?? $item->status }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </main>
</body>
</html>