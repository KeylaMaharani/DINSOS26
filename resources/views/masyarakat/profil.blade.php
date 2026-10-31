    @extends('layouts.masyarakat')

    @section('title', 'Profile - SOLID Dinas Sosial Kota Bogor')
    @section('page_title', 'Profile')
    @section('container', 'max-w-4xl')

    @section('content')
        @php
            $inputCls = 'w-full h-11 px-3 rounded-lg border border-outline-variant bg-white text-sm text-on-surface
                        focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand
                        disabled:bg-surface-container disabled:text-on-surface-variant';
            $readCls = 'w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container text-sm text-on-surface-variant';
            $labelCls = 'text-sm text-on-surface-variant sm:pt-3 sm:text-right';
            $rowCls = 'grid grid-cols-1 sm:grid-cols-[10rem_minmax(0,1fr)] gap-x-5 gap-y-1';

            $tabs = [
                'data' => ['Data Diri', 'person', route('masyarakat.profil')],
                'akun' => ['Akun & Kata Sandi', 'lock', route('masyarakat.password')],
            ];
        @endphp

        <section class="rounded-2xl bg-white ring-1 ring-outline-variant/40 shadow-sm overflow-hidden">

            {{-- Tab --}}
            <nav class="flex border-b border-outline-variant/40 px-3 pt-2 gap-1 overflow-x-auto">
                @foreach ($tabs as $key => [$label, $icon, $url])
                    <a href="{{ $url }}"
                        class="flex items-center gap-2 px-4 py-3 text-sm whitespace-nowrap border-b-2 -mb-px transition-colors
                            {{ $tab === $key
                                ? 'border-brand text-brand font-bold'
                                : 'border-transparent text-on-surface-variant hover:text-brand' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            @if ($tab === 'data')

                <header class="flex items-center gap-3 px-5 py-4 border-b border-outline-variant/40">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-soft text-brand flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">person</span>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-brand leading-tight">Edit Profile</h2>
                        <p class="text-xs text-on-surface-variant">Perbarui alamat dan kontak Anda.</p>
                    </div>
                </header>

                @if (! $pbi)
                    <div class="mx-5 mt-4 flex items-start gap-2 rounded-xl bg-warning-container px-4 py-3 text-sm text-on-warning-container">
                        <span class="material-symbols-outlined text-[20px] shrink-0">info</span>
                        <span>Anda belum memiliki pengajuan PBI APBD, jadi data alamat belum tersedia. Hanya email yang bisa diubah.</span>
                    </div>
                @elseif (! $alamatBisaEdit)
                    <div class="mx-5 mt-4 flex items-start gap-2 rounded-xl bg-warning-container px-4 py-3 text-sm text-on-warning-container">
                        <span class="material-symbols-outlined text-[20px] shrink-0">lock</span>
                        <span>Pengajuan sudah disetujui, alamat tidak dapat diubah lagi. No. telp dan email masih bisa diperbarui.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('masyarakat.profil.update') }}"
                    x-data="{
                        wilayah: @js($wilayah),
                        kec: @js(old('kecamatan', $profil['kecamatan'])),
                        kel: @js(old('desa_kelurahan', $profil['kelurahan'])),
                        get kelOpts() { return this.wilayah[this.kec] || []; },
                        gantiKec() { if (!this.kelOpts.includes(this.kel)) this.kel = ''; }
                    }">
                    @csrf
                    @method('PUT')

                    <div class="px-5 py-6 space-y-4">

                        {{-- NIK (terkunci) --}}
                        <div class="{{ $rowCls }}">
                            <label class="{{ $labelCls }}">NIK</label>
                            <input type="text" value="{{ $profil['nik'] }}" class="{{ $readCls }}" readonly>
                        </div>

                        {{-- Nama Lengkap (terkunci) --}}
                        <div class="{{ $rowCls }}">
                            <label class="{{ $labelCls }}">Nama Lengkap</label>
                            <input type="text" value="{{ $profil['nama'] }}" class="{{ $readCls }}" readonly>
                        </div>

                        {{-- Alamat --}}
                        <div class="{{ $rowCls }}">
                            <label for="alamat" class="{{ $labelCls }}">Alamat Pemohon <span class="text-error">*</span></label>
                            <div>
                                <textarea id="alamat" name="alamat" rows="3" maxlength="1000"
                                    class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-white text-sm text-on-surface
                                        focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand
                                        disabled:bg-surface-container disabled:text-on-surface-variant"
                                    @disabled(! $alamatBisaEdit) required>{{ old('alamat', $profil['alamat']) }}</textarea>
                                @error('alamat') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- RT & RW --}}
                        <div class="{{ $rowCls }}">
                            <label for="rt" class="{{ $labelCls }}">RT <span class="text-error">*</span></label>
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <input id="rt" type="text" name="rt" value="{{ old('rt', $profil['rt']) }}" maxlength="5"
                                        class="{{ $inputCls }} !w-28" @disabled(! $alamatBisaEdit) required>
                                    <label for="rw" class="text-sm text-on-surface-variant sm:ml-6">RW <span class="text-error">*</span></label>
                                    <input id="rw" type="text" name="rw" value="{{ old('rw', $profil['rw']) }}" maxlength="5"
                                        class="{{ $inputCls }} !w-28" @disabled(! $alamatBisaEdit) required>
                                </div>
                                @error('rt') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                @error('rw') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Provinsi (terkunci) --}}
                        <div class="{{ $rowCls }}">
                            <label class="{{ $labelCls }}">Provinsi</label>
                            <input type="text" value="{{ $profil['provinsi'] }}" class="{{ $readCls }}" readonly>
                        </div>

                        {{-- Kab/Kota (terkunci) --}}
                        <div class="{{ $rowCls }}">
                            <label class="{{ $labelCls }}">Kab/Kota</label>
                            <input type="text" value="{{ $profil['kab_kota'] }}" class="{{ $readCls }}" readonly>
                        </div>

                        {{-- Kecamatan --}}
                        <div class="{{ $rowCls }}">
                            <label for="kecamatan" class="{{ $labelCls }}">Kecamatan <span class="text-error">*</span></label>
                            <div>
                                <select id="kecamatan" name="kecamatan" x-model="kec" @change="gantiKec()"
                                    class="{{ $inputCls }}" @disabled(! $alamatBisaEdit) required>
                                    <option value="">-Pilih-</option>
                                    @foreach (array_keys($wilayah) as $k)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>
                                @error('kecamatan') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Desa/Kelurahan --}}
                        <div class="{{ $rowCls }}">
                            <label for="desa_kelurahan" class="{{ $labelCls }}">Desa/Kelurahan <span class="text-error">*</span></label>
                            <div>
                                <select id="desa_kelurahan" name="desa_kelurahan" x-model="kel"
                                    class="{{ $inputCls }}" @disabled(! $alamatBisaEdit) required>
                                    <option value="">-Pilih-</option>
                                    <template x-for="k in kelOpts" :key="k">
                                        <option :value="k" x-text="k" :selected="k === kel"></option>
                                    </template>
                                </select>
                                @error('desa_kelurahan') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- No Telp --}}
                        <div class="{{ $rowCls }}">
                            <label for="no_telp" class="{{ $labelCls }}">No Telp</label>
                            <div>
                                <input id="no_telp" type="text" name="no_telp" value="{{ old('no_telp', $profil['no_telp']) }}"
                                    maxlength="20" inputmode="tel" class="{{ $inputCls }}" @disabled(! $pbi)>
                                @error('no_telp') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="{{ $rowCls }}">
                            <label for="email" class="{{ $labelCls }}">Email <span class="text-error">*</span></label>
                            <div>
                                <input id="email" type="email" name="email" value="{{ old('email', $profil['email']) }}"
                                    maxlength="150" class="{{ $inputCls }}" required>
                                @error('email') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Verifikasi --}}
                        <div class="{{ $rowCls }}">
                            <span class="{{ $labelCls }}">Verifikasi <span class="text-error">*</span></span>
                            <div>
                                <div class="inline-flex items-center gap-3 rounded-lg border border-outline-variant bg-surface px-4 py-3">
                                    <span class="text-sm font-semibold text-on-surface">{{ $captchaA }} + {{ $captchaB }} = ?</span>
                                    <input type="text" name="captcha" inputmode="numeric" autocomplete="off" maxlength="3"
                                        class="w-20 h-10 px-3 rounded-lg border border-outline-variant bg-white text-sm text-center
                                            focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand" required>
                                </div>
                                @error('captcha') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Tombol --}}
                        <div class="{{ $rowCls }}">
                            <span class="hidden sm:block"></span>
                            <div>
                                <button type="submit"
                                    class="h-11 px-8 rounded-lg bg-brand text-sm font-semibold text-white hover:bg-primary transition-colors">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            @else

                @include('masyarakat._akun')

            @endif
        </section>
    @endsection
