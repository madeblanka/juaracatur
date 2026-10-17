@extends('layouts.guest')

@section('title', 'Daftar sebagai Venue')

@section('auth_card_class', 'auth-card-wide')

@section('content')
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-1">Daftarkan Tempatmu</h5>
        <p class="text-muted small mb-0">Pemain bisa memilih tempatmu untuk duel, melihat foto dan lokasinya di peta, serta melihat room yang sedang berlangsung.</p>
    </div>

    <form method="POST" action="{{ route('venue.register.store') }}">
        @csrf

        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-dark rounded-circle">1</span>
            <h6 class="fw-bold mb-0">Akun Pengelola</h6>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label" for="f1-name">Nama Pengelola <span class="text-danger">*</span></label>
                <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama kamu" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="f2-email">Email <span class="text-danger">*</span></label>
                <input id="f2-email" type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="contoh@email.com" required>
                <div class="form-text">Dipakai untuk masuk ke akunmu.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="f3-password">Password <span class="text-danger">*</span></label>
                <input id="f3-password" type="password" name="password" class="form-control" minlength="8" required>
                <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="f4-password-confirmation">Ulangi Password <span class="text-danger">*</span></label>
                <input id="f4-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="8" required>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-dark rounded-circle">2</span>
            <h6 class="fw-bold mb-0">Data Tempat</h6>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label" for="f5-venue-name">Nama Tempat <span class="text-danger">*</span></label>
                <input id="f5-venue-name" type="text" name="venue_name" class="form-control" value="{{ old('venue_name') }}" placeholder="Contoh: GOR Catur Sanur" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="f6-phone">Nomor WhatsApp Tempat</label>
                <input id="f6-phone" type="text" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="20" placeholder="Contoh: 081234567890">
            </div>
            <div class="col-12">
                <label class="form-label" for="f7-address">Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea id="f7-address" name="address" class="form-control" rows="2" maxlength="500" placeholder="Jalan, nomor, kelurahan, kota" required>{{ old('address') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="f8-lokasi-google-maps">Pin Lokasi dari Google Maps <span class="text-danger">*</span></label>
                <input id="f8-lokasi-google-maps" type="text" name="lokasi_google_maps" class="form-control" value="{{ old('lokasi_google_maps') }}" placeholder="Contoh: -8.670458, 115.212629" required>
                <div class="form-text">
                    Buka Google Maps, cari tempatmu, klik kanan tepat pada lokasinya, lalu klik angka paling atas (angkanya otomatis tersalin). Tempel di kolom ini. Bisa juga tempel link Google Maps lengkap dari tempatmu.
                </div>
            </div>
            <div class="col-12">
                <label class="form-label" for="f9-description">Deskripsi Tempat <span class="text-muted">(opsional)</span></label>
                <textarea id="f9-description" name="description" class="form-control" rows="3" maxlength="2000" placeholder="Contoh: Meja catur tersedia 10 set, buka setiap hari 08.00 - 22.00">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="visually-hidden" aria-hidden="true">
            <input type="text" name="hp_website" tabindex="-1" autocomplete="off">
        </div>

        <p class="small text-muted mb-3"><span class="text-danger">*</span> wajib diisi.</p>
        <button class="btn btn-dark btn-lg w-100">Daftar Tempat</button>
        <p class="text-center mt-3 mb-0 small">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </form>
@endsection
