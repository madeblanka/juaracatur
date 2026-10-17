@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <p class="text-muted small mb-3">Kolom yang bertanda <span class="text-danger">*</span> wajib diisi.</p>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf
        <x-honeypot />
        <div class="mb-3">
            <label class="form-label" for="f1-name">Nama <span class="text-danger">*</span></label>
            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f2-email">Email <span class="text-danger">*</span></label>
            <input id="f2-email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f3-phone">Nomor WhatsApp <span class="text-danger">*</span></label>
            <input id="f3-phone" type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" required>
            <div class="form-text">Akan ditampilkan ke lawan setelah room diterima.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f4-birthdate">Tanggal Lahir <span class="text-danger">*</span></label>
            <input id="f4-birthdate" type="date" name="birthdate" class="form-control" value="{{ old('birthdate') }}" max="{{ now()->subDay()->format('Y-m-d') }}" required>
            <div class="form-text">Usia kamu dihitung otomatis. Yang tampil ke pemain lain hanya usianya.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f5-photo">Domisili <span class="text-danger">*</span></label>
            <div class="row g-2">
                <div class="col-12">
                    <select id="sel-provinsi" aria-label="Provinsi" class="form-select form-select-sm" required>
                        <option value="">Pilih provinsi...</option>
                        @foreach ($provinsi as $item)
                            <option value="{{ $item->kode }}" @selected(substr((string) old('kode_wilayah'), 0, 2) === $item->kode)>{{ $item->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <select id="sel-kabupaten" aria-label="Kabupaten atau kota" class="form-select form-select-sm" disabled>
                        <option value="">Pilih kabupaten/kota...</option>
                    </select>
                </div>
                <div class="col-12">
                    <select id="sel-kecamatan" aria-label="Kecamatan" class="form-select form-select-sm" disabled>
                        <option value="">Pilih kecamatan...</option>
                    </select>
                </div>
                <div class="col-12">
                    <select name="kode_wilayah" id="sel-kelurahan" aria-label="Kelurahan atau desa" class="form-select form-select-sm" disabled required>
                        <option value="">Pilih kelurahan/desa...</option>
                    </select>
                </div>
            </div>
            <div class="form-text">Pilih berurutan dari provinsi sampai kelurahan/desa.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Foto Profil <span class="text-danger">*</span></label>
            <input id="f5-photo" type="file" name="photo" class="form-control" accept="image/*" required>
            <div class="form-text">Wajib diunggah. Maksimal 2MB (jpg, png, atau webp).</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f6-password">Password <span class="text-danger">*</span></label>
            <input id="f6-password" type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="f7-password-confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
            <input id="f7-password-confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Daftar &amp; Dapatkan 1000 Poin</button>
    </form>
    <p class="text-center mt-3 mb-0 small">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection

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
