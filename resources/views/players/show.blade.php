@extends('layouts.app')

@section('title', $player->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            @if ($player->photoUrl())
                <img src="{{ $player->photoUrl() }}" alt="{{ $player->name }}" class="rounded-circle" width="72" height="72" style="object-fit: cover;">
            @else
                <div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                    <i class="bi bi-person-fill fs-2"></i>
                </div>
            @endif
            <div>
                <h4 class="fw-bold mb-0">{{ $player->name }}</h4>
                <span class="text-muted small d-block">Bergabung {{ $player->created_at->translatedFormat('d M Y') }}</span>
                @php $usia = $player->usia(); $domisili = $player->domisiliLabel(); @endphp
                <span class="text-muted small">
                    @if ($usia !== null) <i class="bi bi-calendar3"></i> {{ $usia }} tahun @endif
                    @if ($usia !== null && $domisili) &middot; @endif
                    @if ($domisili) <i class="bi bi-geo-alt"></i> {{ $domisili }} @endif
                </span>
            </div>
        </div>
        @if ($player->id === auth()->id())
            <a href="{{ route('settings.edit') }}" class="btn btn-sm btn-outline-dark">Edit Profil</a>
        @elseif ($player->whatsappUrl())
            <a href="{{ $player->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-success"><i class="bi bi-whatsapp"></i> Chat WhatsApp</a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold text-success"><x-points-link :user="$player" /></div><div class="small text-muted">Poin</div></div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $wins }}</div><div class="small text-muted">Menang</div></div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $losses }}</div><div class="small text-muted">Kalah</div></div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ $totalMatches }}</div><div class="small text-muted">Total Duel</div></div></div>
        </div>
    </div>

    @if ($player->id === auth()->id())
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">Data Pribadi <span class="small text-muted fw-normal">&middot; hanya kamu yang bisa melihat</span></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email</span><span>{{ $player->email }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Nomor HP</span><span>{{ $player->phone ?: 'Belum diisi' }}</span></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tanggal Lahir</span><span>{{ $player->birthdate?->translatedFormat('d F Y') ?? 'Belum diisi' }}</span></li>
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Riwayat Duel</div>
                <div class="list-group list-group-flush">
                    @forelse ($recentMatches as $room)
                        @php $isChallenger = $room->challenger_id === $player->id; $opponent = $isChallenger ? $room->opponent : $room->challenger; $won = $room->winner_id === $player->id; @endphp
                        <a href="{{ route('rooms.show', $room) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">vs {{ $opponent->name }}</div>
                                <div class="small text-muted">{{ $room->venue->name }} &middot; {{ $room->updated_at->translatedFormat('d M Y') }}</div>
                            </div>
                            <span class="badge bg-{{ $won ? 'success' : 'danger' }}">{{ $won ? 'Menang' : 'Kalah' }} {{ $won ? '+' : '-' }}{{ $room->wager_points }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Belum ada riwayat duel.</div>
                    @endforelse
                </div>
                @if ($recentMatches->hasPages())
                    <div class="card-body">{{ $recentMatches->links() }}</div>
                @endif
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Riwayat Turnamen</div>
                <div class="list-group list-group-flush">
                    @forelse ($tournamentHistory as $participation)
                        <a href="{{ route('tournaments.show', $participation->tournament) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $participation->tournament->name }}</span>
                            <span class="small text-muted">{{ $participation->wins }}M / {{ $participation->losses }}K / {{ $participation->draws }}S</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Belum pernah ikut turnamen.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
