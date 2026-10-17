@extends('layouts.app')

@section('title', 'Cari Lawan')

@section('content')
    <h4 class="fw-bold mb-4">Cari Lawan</h4>

    <form method="GET" action="{{ route('players.index') }}" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Cari nama atau email pemain..." value="{{ $q }}">
        </div>
        <div class="col-md-4">
            <select name="sort" class="form-select" aria-label="Urutkan pemain">
                <option value="">Urutkan: Terbaru</option>
                <option value="wilayah" @selected($sort === 'wilayah')>Urutkan: Wilayah</option>
                <option value="email" @selected($sort === 'email')>Urutkan: Email</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Cari</button>
        </div>
    </form>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($players as $player)
                <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-start gap-3 min-w-0">
                        <x-player-avatar :user="$player" :size="56" />
                        <div class="min-w-0">
                            <a href="{{ route('players.show', $player) }}" class="fw-semibold text-decoration-none text-break">{{ $player->name }}</a>
                            <div class="small text-muted text-break">{{ $player->email }}</div>
                            <x-player-meta :user="$player" />
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-success"><x-points-link :user="$player" /> poin</span>
                        @if ($player->id !== auth()->id())
                            <a href="{{ route('rooms.create', ['opponent_id' => $player->id]) }}" class="btn btn-sm btn-outline-dark">Tantang</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted">Tidak ada pemain yang cocok.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ $players->links() }}</div>
@endsection
