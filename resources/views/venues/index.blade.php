@extends('layouts.app')

@section('title', 'Cari Tempat Bertanding')

@section('content')
    <h4 class="fw-bold mb-4">Cari Tempat Bertanding</h4>

    <form method="GET" action="{{ route('venues.index') }}" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Cari nama tempat..." value="{{ $q }}">
        </div>
        <div class="col-md-4">
            <select name="sort" class="form-select">
                <option value="">Urutkan: Nama</option>
                <option value="wilayah" @selected($sort === 'wilayah')>Urutkan: Wilayah</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Cari</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse ($venues as $venue)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">{{ $venue->name }}</h6>
                        <p class="small text-muted mb-1"><i class="bi bi-geo-alt"></i> {{ $venue->address }}</p>
                        @if ($venue->wilayah)
                            <p class="small text-muted mb-2">Wilayah: {{ $venue->wilayah }}</p>
                        @endif
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-light text-dark">{{ $venue->rooms_count }} room</span>
                            @if ($venue->ongoing_tournaments_count > 0)
                                <span class="badge bg-primary">{{ $venue->ongoing_tournaments_count }} turnamen berjalan</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('venues.show', $venue) }}" class="btn btn-sm btn-outline-secondary flex-fill">Detail &amp; Lobby</a>
                            <a href="{{ route('rooms.create', ['venue_id' => $venue->id]) }}" class="btn btn-sm btn-outline-dark flex-fill">Pilih Tempat Ini</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Tidak ada tempat yang cocok.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $venues->links() }}</div>
@endsection
