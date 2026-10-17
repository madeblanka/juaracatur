@extends('layouts.app')

@section('title', 'Turnamen')

@section('content')
    <h4 class="fw-bold mb-3">Turnamen</h4>

    <ul class="nav nav-pills mb-4">
        <li class="nav-item"><a class="nav-link {{ ! $activeStatus ? 'active' : '' }}" href="{{ route('tournaments.index') }}">Semua</a></li>
        <li class="nav-item"><a class="nav-link {{ $activeStatus === 'upcoming' ? 'active' : '' }}" href="{{ route('tournaments.index', ['status' => 'upcoming']) }}">Akan Datang</a></li>
        <li class="nav-item"><a class="nav-link {{ $activeStatus === 'ongoing' ? 'active' : '' }}" href="{{ route('tournaments.index', ['status' => 'ongoing']) }}">Sedang Berlangsung</a></li>
        <li class="nav-item"><a class="nav-link {{ $activeStatus === 'completed' ? 'active' : '' }}" href="{{ route('tournaments.index', ['status' => 'completed']) }}">Selesai</a></li>
    </ul>

    <div class="row g-3">
        @forelse ($tournaments as $tournament)
            <div class="col-md-4">
                <div class="card h-100">
                    @if ($tournament->flyerUrl())
                        <img src="{{ $tournament->flyerUrl() }}" alt="Flyer {{ $tournament->name }}" class="card-img-top" style="aspect-ratio: 4 / 5; object-fit: cover;">
                    @else
                        <div class="bg-light text-muted d-flex flex-column align-items-center justify-content-center" style="aspect-ratio: 4 / 5;">
                            <i class="bi bi-trophy fs-1"></i>
                            <div class="small mt-2">Flyer belum ada</div>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold mb-1">{{ $tournament->name }}</h6>
                        <p class="small text-muted mb-2">{{ $tournament->participants_count }} peserta &middot; {{ $tournament->formatLabel() }}</p>
                        <p class="small mb-2"><i class="bi bi-coin"></i> {{ $tournament->entry_points > 0 ? number_format($tournament->entry_points).' poin' : 'Gratis' }}</p>
                        @if ($tournament->description)
                            <p class="small mb-3">{{ \Illuminate\Support\Str::limit($tournament->description, 110) }}</p>
                        @endif
                        <div class="mt-auto">
                            <span class="badge bg-{{ match($tournament->status) { 'draft' => 'secondary', 'ongoing' => 'primary', 'completed' => 'success', default => 'dark' } }} mb-3">{{ strtoupper($tournament->status) }}</span>
                            <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-sm btn-outline-dark w-100">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Belum ada turnamen yang dibuat admin.</div>
        @endforelse
    </div>
@endsection
