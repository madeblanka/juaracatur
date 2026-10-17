@extends('layouts.app')

@section('title', $tournament->name)

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-2">{{ $tournament->name }}</h4>
            <span class="badge bg-{{ match($tournament->status) { 'draft' => 'secondary', 'ongoing' => 'primary', 'completed' => 'success', default => 'dark' } }}">{{ strtoupper($tournament->status) }}</span>
            <span class="badge bg-info text-dark">{{ $tournament->formatLabel() }}</span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            @if ($tournament->flyerUrl())
                <img src="{{ $tournament->flyerUrl() }}" alt="Flyer {{ $tournament->name }}" class="img-fluid rounded shadow-sm w-100">
            @else
                <div class="card bg-light text-center text-muted p-5 h-100 justify-content-center">
                    <i class="bi bi-image fs-1"></i>
                    <div class="small mt-2">Flyer belum tersedia</div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold">Tentang Turnamen</h6>
                    <p class="mb-3" style="white-space: pre-line;">{{ $tournament->description ?: 'Belum ada deskripsi.' }}</p>

                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1"><i class="bi bi-diagram-3"></i> <strong>Format:</strong> {{ $tournament->isDoubleElimination() ? 'Bracket dengan lower bracket (kalah 1x turun, kalah 2x gugur)' : ($tournament->isSingleElimination() ? 'Single Elimination (kalah sekali langsung gugur)' : $tournament->roundRobinLabel().' (semua saling bertanding, setiap pasangan main '.$tournament->rr_rounds.'x)') }}</li>
                        @if ($tournament->starts_at)
                            <li class="mb-1"><i class="bi bi-calendar-event"></i> <strong>Mulai:</strong> {{ $tournament->starts_at->translatedFormat('l, d M Y H:i') }}</li>
                        @endif
                        <li class="mb-1"><i class="bi bi-people"></i> <strong>Peserta:</strong> {{ $tournament->participants->count() }} pemain</li>
                        @if ($tournament->venue)
                            <li><i class="bi bi-geo-alt"></i> <strong>Tempat:</strong> {{ $tournament->venue->name }}</li>
                        @endif
                    </ul>

                    @if ($tournament->status === 'draft')
                        @php
                            $sudahDaftar = $tournament->participants->contains('user_id', auth()->id());
                            $fee = (int) $tournament->entry_points;
                            $poinCukup = auth()->user()->points >= $fee;
                        @endphp
                        <div class="border rounded p-3 mt-3">
                            @if ($sudahDaftar)
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <span><i class="bi bi-check-circle text-success"></i> <strong>Kamu sudah terdaftar</strong> di turnamen ini.</span>
                                    <form method="POST" action="{{ route('tournaments.leave', $tournament) }}" onsubmit="return confirm('Keluar dari turnamen?{{ $fee > 0 ? ' Biaya '.number_format($fee).' poin akan dikembalikan.' : '' }}');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Keluar dari Turnamen</button>
                                    </form>
                                </div>
                            @else
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <span>
                                        <strong>Biaya daftar:</strong>
                                        {{ $fee > 0 ? number_format($fee).' poin' : 'Gratis' }}
                                        <span class="small text-muted d-block">Poinmu sekarang: {{ number_format(auth()->user()->points) }}</span>
                                    </span>
                                    @if ($poinCukup)
                                        <form method="POST" action="{{ route('tournaments.join', $tournament) }}" onsubmit="return confirm('Daftar ke turnamen ini?{{ $fee > 0 ? ' '.number_format($fee).' poin akan dipotong dari poinmu.' : '' }}');">
                                            @csrf
                                            <button class="btn btn-dark">Daftar{{ $fee > 0 ? ' ('.number_format($fee).' poin)' : '' }}</button>
                                        </form>
                                    @else
                                        <span class="small text-danger">Poinmu tidak cukup untuk mendaftar.</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                    @if ($myInvite)
                        <div class="alert alert-warning mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span><strong>Kamu diundang</strong> mengikuti turnamen ini.</span>
                            <span class="d-flex gap-2">
                                <form method="POST" action="{{ route('tournaments.invite.respond', $tournament) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="accept">
                                    <button class="btn btn-sm btn-success">Terima</button>
                                </form>
                                <form method="POST" action="{{ route('tournaments.invite.respond', $tournament) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="decline">
                                    <button class="btn btn-sm btn-outline-secondary">Tolak</button>
                                </form>
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($tournament->venue)
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-geo-alt"></i> Lokasi: {{ $tournament->venue->name }}</span>
                <a href="{{ $tournament->venue->mapLink() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark">Buka di Google Maps</a>
            </div>
            <iframe src="{{ $tournament->venue->mapEmbedUrl() }}" class="w-100 border-0" style="height: 320px;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta {{ $tournament->venue->name }}"></iframe>
            <div class="card-body small text-muted">{{ $tournament->venue->address }}</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Klasemen</div>
                <div class="table-responsive"><table class="table mb-0">
                    <thead>
                        <tr><th>#</th><th>Pemain</th><th>M</th><th>K</th><th>S</th><th>Poin</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($standings as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <x-player-avatar :user="$p->user" :size="36" />
                                        <div>
                                            <div class="fw-semibold">{{ $p->user->name }}</div>
                                            <x-player-meta :user="$p->user" />
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $p->wins }}</td>
                                <td>{{ $p->losses }}</td>
                                <td>{{ $p->draws }}</td>
                                <td class="fw-bold">{{ $p->points() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">Belum ada peserta.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Bagan Pertandingan</div>
                <div class="card-body">
                    @if ($myMatch)
                        @php
                            $lawanId = $myMatch->player1_id === auth()->id() ? $myMatch->player2_id : $myMatch->player1_id;
                            $sudahLapor = $myMatch->player1_id === auth()->id() ? $myMatch->player1_reported_winner_id !== null : $myMatch->player2_reported_winner_id !== null;
                        @endphp
                        <div class="alert alert-primary mb-3">
                            <div class="fw-semibold mb-2">Pertandingan kamu melawan {{ $myMatch->player1_id === auth()->id() ? $myMatch->player2->name : $myMatch->player1->name }}</div>
                            @if ($sudahLapor)
                                <div class="small mb-0">Laporanmu sudah terkirim. Menunggu laporan dari lawan.</div>
                            @else
                                <form method="POST" action="{{ route('tournaments.matches.report', [$tournament, $myMatch]) }}" class="d-flex flex-wrap gap-2 align-items-center">
                                    @csrf
                                    <span class="small me-1">Siapa pemenangnya?</span>
                                    <button type="submit" name="winner_id" value="{{ auth()->id() }}" class="btn btn-sm btn-success">Saya menang</button>
                                    <button type="submit" name="winner_id" value="{{ $lawanId }}" class="btn btn-sm btn-outline-danger">Lawan menang</button>
                                    @if ($tournament->isRoundRobin())
                                        <button type="submit" name="winner_id" value="0" class="btn btn-sm btn-outline-secondary">Seri</button>
                                    @endif
                                </form>
                                <div class="small text-muted mt-2">Hasil dicatat jika kamu dan lawan melaporkan hasil yang sama. Jika berbeda, admin akan meninjau.</div>
                            @endif
                        </div>
                    @endif
                    @if ($myDisputedMatch)
                        <div class="alert alert-warning mb-3">Pertandingan kamu melawan {{ $myDisputedMatch->player1_id === auth()->id() ? $myDisputedMatch->player2->name : $myDisputedMatch->player1->name }} sedang sengketa. Admin akan meninjau hasilnya.</div>
                    @endif
                    <x-bracket-chart :tournament="$tournament" />
                </div>
            </div>
        </div>
    </div>
@endsection
