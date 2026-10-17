@extends('layouts.app')

@section('title', 'Dashboard Tempat')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $venue->name }}</h4>
            <a href="{{ route('venues.show', $venue) }}" class="small">Lihat halaman publik tempatmu &rarr;</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Profil &amp; Lokasi</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('venue.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="f1-name">Nama Tempat</label>
                            <input id="f1-name" type="text" name="name" class="form-control" value="{{ old('name', $venue->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f2-address">Alamat Lengkap</label>
                            <textarea id="f2-address" name="address" class="form-control" rows="2" required>{{ old('address', $venue->address) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f3-phone">Nomor Telepon / WA</label>
                            <input id="f3-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $venue->phone) }}" maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f4-lokasi-google-maps">Pin Lokasi dari Google Maps <span class="text-danger">*</span></label>
                            <input id="f4-lokasi-google-maps" type="text" name="lokasi_google_maps" class="form-control" value="{{ old('lokasi_google_maps', $venue->hasCoordinates() ? $venue->latitude.', '.$venue->longitude : '') }}" placeholder="Contoh: -8.670458, 115.212629" required>
                            <div class="form-text">
                                Buka Google Maps, cari tempatmu, klik kanan tepat pada lokasinya, lalu klik angka paling atas (angkanya otomatis tersalin). Tempel di kolom ini. Bisa juga tempel link Google Maps lengkap dari tempatmu.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="f5-description">Deskripsi</label>
                            <textarea id="f5-description" name="description" class="form-control" rows="3">{{ old('description', $venue->description) }}</textarea>
                        </div>
                        <button class="btn btn-dark w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Galeri ({{ $photos->count() }}/10)</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('venue.photos.store') }}" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <input type="file" name="photos[]" aria-label="Tambah foto tempat" class="form-control mb-2" accept="image/*" multiple required>
                        <div class="form-text mb-2">Maksimal 10 foto, masing-masing 4MB (jpg/png/webp).</div>
                        <button class="btn btn-outline-dark btn-sm">Unggah Foto</button>
                    </form>

                    <div class="row g-2">
                        @forelse ($photos as $photo)
                            <div class="col-4">
                                <div class="position-relative">
                                    <img src="{{ $photo->url() }}" alt="Foto {{ $venue->name }}" class="img-fluid rounded" style="aspect-ratio: 1; object-fit: cover; width: 100%;">
                                    <form method="POST" action="{{ route('venue.photos.destroy', $photo) }}" class="position-absolute top-0 end-0 m-1" onsubmit="return confirm('Hapus foto ini?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger py-0 px-1">&times;</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted small">Belum ada foto. Tambahkan foto agar pemain lebih mengenal tempatmu.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Lobby Saat Ini</div>
                <ul class="list-group list-group-flush">
                    @forelse ($lobby as $room)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $room->challenger->name }} {{ $room->opponent ? 'vs '.$room->opponent->name : '(menunggu lawan)' }}</div>
                                <div class="small text-muted">Taruhan {{ number_format($room->wager_points) }} poin &middot; {{ $room->status === 'open' ? 'Terbuka' : 'Berlangsung' }}</div>
                            </div>
                            <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-dark">Detail Room</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada room di tempatmu.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
