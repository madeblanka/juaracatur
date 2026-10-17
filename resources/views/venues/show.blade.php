@extends('layouts.app')

@section('title', $venue->name)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $venue->name }}</h4>
            <div class="small text-muted"><i class="bi bi-geo-alt"></i> {{ $venue->address }}</div>
            @if ($venue->phone)
                <div class="small text-muted"><i class="bi bi-telephone"></i> {{ $venue->phone }}</div>
            @endif
        </div>
        <a href="{{ route('venues.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Galeri</div>
                <div class="card-body">
                    @if ($venue->photos->isEmpty())
                        <p class="text-muted small mb-0">Pengelola belum menambahkan foto.</p>
                    @else
                        <div id="venueGallery" class="carousel slide" data-bs-ride="false">
                            <div class="carousel-inner rounded">
                                @foreach ($venue->photos as $photo)
                                    <div class="carousel-item @if ($loop->first) active @endif">
                                        <img src="{{ $photo->url() }}" class="d-block w-100" alt="Foto {{ $venue->name }}" style="max-height: 360px; object-fit: cover;" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                            @if ($venue->photos->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#venueGallery" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#venueGallery" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white fw-semibold">Lokasi di Peta</div>
                <div class="card-body p-0">
                    <iframe src="{{ $venue->mapEmbedUrl() }}" class="w-100 border-0 rounded-bottom" style="height: 320px;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta {{ $venue->name }}"></iframe>
                </div>
                <div class="card-footer bg-white small">
                    <a href="{{ $venue->mapLink() }}" target="_blank" rel="noopener">Buka di Google Maps &rarr;</a>
                </div>
            </div>

            @if ($venue->description)
                <div class="card">
                    <div class="card-header bg-white fw-semibold">Tentang Tempat</div>
                    <div class="card-body">{{ $venue->description }}</div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Lobby Room</div>
                <ul class="list-group list-group-flush">
                    @forelse ($lobby as $room)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $room->challenger->name }} {{ $room->opponent ? 'vs '.$room->opponent->name : '(menunggu lawan)' }}</div>
                                <div class="small text-muted">Taruhan {{ number_format($room->wager_points) }} poin &middot; <span class="badge bg-{{ $room->status === 'open' ? 'secondary' : 'primary' }}">{{ $room->status === 'open' ? 'Terbuka' : 'Berlangsung' }}</span></div>
                            </div>
                            <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-dark">Lihat</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada room aktif di tempat ini.</li>
                    @endforelse
                </ul>
                <div class="card-footer bg-white">
                    <a href="{{ route('rooms.create', ['venue_id' => $venue->id]) }}" class="btn btn-dark btn-sm w-100">Buat Tantangan di Sini</a>
                </div>
            </div>
        </div>
    </div>
@endsection
