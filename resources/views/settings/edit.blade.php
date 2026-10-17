@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
    <h4 class="fw-bold mb-4">Pengaturan Akun</h4>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Profil</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 text-center">
                            @if ($user->photoUrl())
                                <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}" class="rounded-circle mb-2" width="96" height="96" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 96px; height: 96px;">
                                    <i class="bi bi-person-fill fs-1"></i>
                                </div>
                            @endif
                            <input type="file" name="photo" id="foto-profil" aria-label="Foto profil" class="form-control" accept="image/*" @unless ($user->photo) required @endunless>
                            @unless ($user->photo)<div class="form-text text-danger">Foto profil wajib diunggah.</div>@endunless
                            <div class="form-text">Foto profil, maksimal 2MB (jpg/png/webp).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama <span class="text-danger">*</span></label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-email">Email <span class="text-danger">*</span></label>
                            <input id="f2-email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f3-birthdate">Tanggal Lahir</label>
                            <input id="f3-birthdate" type="date" name="birthdate" class="form-control" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}" max="{{ now()->subDay()->format('Y-m-d') }}">
                            <div class="form-text">Usia kamu dihitung otomatis dari tanggal lahir. Yang tampil ke pemain lain hanya usianya.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f4-phone">Domisili</label>
                            <div class="row g-2">
                                <div class="col-12">
                                    <select id="sel-provinsi" aria-label="Provinsi" class="form-select form-select-sm">
                                        <option value="">Pilih provinsi...</option>
                                        @foreach ($provinsi as $item)
                                            <option value="{{ $item->kode }}" @selected(substr((string) $user->kode_wilayah, 0, 2) === $item->kode)>{{ $item->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <select id="sel-kabupaten" aria-label="Kabupaten atau kota" class="form-select form-select-sm" @disabled($kabupatenOptions->isEmpty())>
                                        <option value="">Pilih kabupaten/kota...</option>
                                        @foreach ($kabupatenOptions as $item)
                                            <option value="{{ $item->kode }}" @selected(substr((string) $user->kode_wilayah, 0, 5) === $item->kode)>{{ $item->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <select id="sel-kecamatan" aria-label="Kecamatan" class="form-select form-select-sm" @disabled($kecamatanOptions->isEmpty())>
                                        <option value="">Pilih kecamatan...</option>
                                        @foreach ($kecamatanOptions as $item)
                                            <option value="{{ $item->kode }}" @selected(substr((string) $user->kode_wilayah, 0, 8) === $item->kode)>{{ $item->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <select name="kode_wilayah" id="sel-kelurahan" aria-label="Kelurahan atau desa" class="form-select form-select-sm" @disabled($kelurahanOptions->isEmpty())>
                                        <option value="">Pilih kelurahan/desa...</option>
                                        @foreach ($kelurahanOptions as $item)
                                            <option value="{{ $item->kode }}" @selected($user->kode_wilayah === $item->kode)>{{ $item->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-text">Pilih berurutan dari provinsi sampai kelurahan/desa. Dipakai untuk pencarian lawan berdasarkan wilayah.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor WhatsApp</label>
                            <input id="f4-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
                            <div class="form-text">Akan ditampilkan ke lawan setelah room diterima, bisa diklik langsung ke WhatsApp.</div>
                        </div>
                        <button class="btn btn-dark w-100">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Ganti Password</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="f5-current-password">Password Saat Ini</label>
                            <input id="f5-current-password" type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f6-password">Password Baru</label>
                            <input id="f6-password" type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f7-password-confirmation">Konfirmasi Password Baru</label>
                            <input id="f7-password-confirmation" type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button class="btn btn-dark w-100">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const endpoint = '{{ route('wilayah.children') }}';
        const provinsi = document.getElementById('sel-provinsi');
        const kabupaten = document.getElementById('sel-kabupaten');
        const kecamatan = document.getElementById('sel-kecamatan');
        const kelurahan = document.getElementById('sel-kelurahan');

        function resetSelect(select, placeholder) {
            select.innerHTML = '';
            select.add(new Option(placeholder, ''));
            select.disabled = true;
        }

        async function loadChildren(select, parentKode, placeholder) {
            resetSelect(select, placeholder);
            if (!parentKode) return;

            const res = await fetch(endpoint + '?parent=' + encodeURIComponent(parentKode), { headers: { Accept: 'application/json' } });
            if (!res.ok) return;

            (await res.json()).forEach(function (item) {
                select.add(new Option(item.nama, item.kode));
            });
            select.disabled = false;
        }

        provinsi.addEventListener('change', function () {
            resetSelect(kecamatan, 'Pilih kecamatan...');
            resetSelect(kelurahan, 'Pilih kelurahan/desa...');
            loadChildren(kabupaten, provinsi.value, 'Pilih kabupaten/kota...');
        });

        kabupaten.addEventListener('change', function () {
            resetSelect(kelurahan, 'Pilih kelurahan/desa...');
            loadChildren(kecamatan, kabupaten.value, 'Pilih kecamatan...');
        });

        kecamatan.addEventListener('change', function () {
            loadChildren(kelurahan, kecamatan.value, 'Pilih kelurahan/desa...');
        });
    })();
    </script>
    @endpush
@endsection
