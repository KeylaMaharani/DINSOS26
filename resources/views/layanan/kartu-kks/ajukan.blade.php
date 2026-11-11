@extends('layouts.admin')

@section('title', 'Tambah Ajuan Kartu KKS - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Kartu KKS — Tambah Ajuan')

@php
    $inputClass = 'w-full px-3 py-2 rounded-xl border border-outline-variant/40 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand';
    $fileClass = 'block w-full text-xs text-on-surface-variant file:mr-3 file:rounded file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-brand file:font-semibold';
    $labelClass = 'block text-xs font-medium text-on-surface-variant mb-1';
    $errClass = 'text-xs text-error mt-1';
    $isHilang = old('masalah_kartu') === 'hilang';
@endphp

@section('content')
    <div class="space-y-5 max-w-5xl">

        <a href="{{ route('kartu-kks.ajuan') }}"
            class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-brand">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke daftar Ajuan
        </a>

        @if ($errors->any())
            <div class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <span>Periksa kembali kolom yang ditandai merah di bawah.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('layanan.kartu-kks.ajukan.store') }}" enctype="multipart/form-data"
            class="space-y-5">
            @csrf

            {{-- Data Pemohon --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant/40 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">badge</span>
                        Data Pemohon
                    </h3>
                    <a href="{{ route('layanan.kartu-kks.riwayat') }}"
                        class="text-xs font-semibold text-brand hover:underline">Lihat Riwayat Pengajuan &rarr;</a>
                </div>

                <div class="p-5 grid grid-cols-1 gap-y-4 max-w-2xl">
                    <div>
                        <label class="{{ $labelClass }}">NIK <span class="text-error">*</span></label>
                        <input type="text" name="nik" maxlength="20" value="{{ old('nik') }}" class="{{ $inputClass }}" />
                        @error('nik') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Nama Lengkap <span class="text-error">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="{{ $inputClass }}" />
                        @error('nama') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Jenis Kelamin <span class="text-error">*</span></label>
                        <select name="jenis_kelamin" class="{{ $inputClass }}">
                            <option value="">-Pilih-</option>
                            <option value="L" @selected(old('jenis_kelamin') == 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin') == 'P')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Status Perkawinan <span class="text-error">*</span></label>
                        <select name="status_perkawinan" class="{{ $inputClass }}">
                            <option value="">-Pilih-</option>
                            @foreach (['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'] as $opt)
                                <option value="{{ $opt }}" @selected(old('status_perkawinan') == $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('status_perkawinan') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Tempat Lahir <span class="text-error">*</span></label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="{{ $inputClass }}" />
                        @error('tempat_lahir') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Tanggal Lahir <span class="text-error">*</span></label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="{{ $inputClass }}" />
                        @error('tanggal_lahir') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Agama <span class="text-error">*</span></label>
                        <select name="agama" class="{{ $inputClass }}">
                            <option value="">-Pilih-</option>
                            @foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghuchu'] as $opt)
                                <option value="{{ $opt }}" @selected(old('agama') == $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('agama') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Pekerjaan <span class="text-error">*</span></label>
                        <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" class="{{ $inputClass }}" />
                        @error('pekerjaan') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">No. PKH <span class="text-error">*</span></label>
                        <input type="text" name="no_pkh" value="{{ old('no_pkh') }}" class="{{ $inputClass }}" />
                        @error('no_pkh') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">No. KK <span class="text-error">*</span></label>
                        <input type="text" name="no_kk" maxlength="20" value="{{ old('no_kk') }}" class="{{ $inputClass }}" />
                        @error('no_kk') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">No. Kartu KKS <span class="text-error">*</span></label>
                        <input type="text" name="no_kartu_kks" value="{{ old('no_kartu_kks') }}" class="{{ $inputClass }}" />
                        @error('no_kartu_kks') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">No. Rekening <span class="text-error">*</span></label>
                        <input type="text" name="no_rekening" maxlength="30" value="{{ old('no_rekening') }}" class="{{ $inputClass }}" />
                        @error('no_rekening') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Alamat <span class="text-error">*</span></label>
                        <textarea name="alamat" rows="2" class="{{ $inputClass }}">{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Kelurahan <span class="text-error">*</span></label>
                        <input type="text" name="kelurahan" value="{{ old('kelurahan') }}" class="{{ $inputClass }}" />
                        @error('kelurahan') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Masalah Kartu <span class="text-error">*</span></label>
                        <select name="masalah_kartu" id="masalah_kartu" class="{{ $inputClass }}">
                            <option value="">-Pilih-</option>
                            @foreach (['terblokir', 'rusak', 'hilang', 'masa berlaku habis', 'nama tidak sesuai', 'keterangan lainnya'] as $opt)
                                <option value="{{ $opt }}" @selected(old('masalah_kartu') == $opt)>{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                        @error('masalah_kartu') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div id="wrap-nomor-kehilangan" class="{{ $isHilang ? '' : 'hidden' }}">
                        <label class="{{ $labelClass }}">Nomor Laporan Kehilangan Polisi <span class="text-error">*</span></label>
                        <input type="text" name="nomor_kehilangan_polisi" value="{{ old('nomor_kehilangan_polisi') }}" class="{{ $inputClass }}" />
                        @error('nomor_kehilangan_polisi') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Lampiran --}}
            <div class="bg-white rounded-3xl shadow-[0_8px_24px_rgba(10,92,168,0.10)] overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant/40 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-brand">attach_file</span>
                        Lampiran
                    </h3>
                    <span class="text-[11px] text-on-surface-variant">JPG, PNG, PDF, maks. 2 MB per file</span>
                </div>

                <div class="p-5 grid grid-cols-1 gap-y-4 max-w-2xl">
                    <div>
                        <label class="{{ $labelClass }}">Scan KTP <span class="text-error">*</span></label>
                        <input type="file" name="scan_ktp" accept=".jpg,.jpeg,.png,.pdf" class="{{ $fileClass }}" />
                        @error('scan_ktp') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Scan Kartu Keluarga <span class="text-error">*</span></label>
                        <input type="file" name="scan_kartu_keluarga" accept=".jpg,.jpeg,.png,.pdf" class="{{ $fileClass }}" />
                        @error('scan_kartu_keluarga') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Scan Kartu KKS <span class="text-error">*</span></label>
                        <input type="file" name="scan_kartu_kks" accept=".jpg,.jpeg,.png,.pdf" class="{{ $fileClass }}" />
                        @error('scan_kartu_kks') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Screenshot DTSEN <span class="text-error">*</span></label>
                        <input type="file" name="screenshot_dtsen" accept=".jpg,.jpeg,.png,.pdf" class="{{ $fileClass }}" />
                        @error('screenshot_dtsen') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                    <div id="wrap-surat-kehilangan" class="{{ $isHilang ? '' : 'hidden' }}">
                        <label class="{{ $labelClass }}">Surat Kehilangan (Kepolisian) <span class="text-error">*</span></label>
                        <input type="file" name="surat_kehilangan_polisi" accept=".jpg,.jpeg,.png,.pdf" class="{{ $fileClass }}" />
                        @error('surat_kehilangan_polisi') <p class="{{ $errClass }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-brand text-white hover:opacity-90 text-sm font-bold shadow-md transition-all">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.getElementById('masalah_kartu').addEventListener('change', function () {
                var hilang = this.value === 'hilang';
                document.getElementById('wrap-nomor-kehilangan').classList.toggle('hidden', !hilang);
                document.getElementById('wrap-surat-kehilangan').classList.toggle('hidden', !hilang);
            });
        </script>
    @endpush
@endsection