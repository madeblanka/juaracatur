<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Juaracatur - Arena Duel Catur &amp; Turnamen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: radial-gradient(circle at 50% 0%, #2b0505 0%, #0d0d0d 55%, #000 100%); color: #fff; min-height: 100vh; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .brand-word {
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 800;
            font-style: italic;
            letter-spacing: 1.5px;
            font-size: 1.3rem;
            color: #fff;
            text-shadow: 0 0 10px rgba(224,70,58,.55);
        }
        .points-badge { font-size: .95rem; }
        .hero { padding: 6rem 0 4rem; }
        .hero-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 800;
            font-style: italic;
            letter-spacing: 2px;
            text-shadow: 0 0 24px rgba(224,70,58,.6);
        }
        .feature-card { background: rgba(255,255,255,.05); border: 1px solid rgba(224,70,58,.25); border-radius: .5rem; }
        .feature-card:hover { border-color: rgba(224,70,58,.6); }
        .list-row { text-decoration: none; color: inherit; display: flex; justify-content: space-between; align-items: center; padding: .5rem 0; border-bottom: 1px solid rgba(255,255,255,.08); }
        .list-row:hover { color: #fff; }
        .list-row:last-child { border-bottom: none; }
        .blink-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            margin-right: 6px;
            animation: blink-pulse 1.4s ease-in-out infinite;
        }
        @keyframes blink-pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34,197,94,.6); }
            50% { opacity: .35; box-shadow: 0 0 0 4px rgba(34,197,94,0); }
        }
    </style>
</head>
<body>
    @auth
        @include('layouts.partials.navbar')
    @endauth

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
        @endif
        @if (auth()->user()?->role === 'player' && ! auth()->user()->hasVerifiedEmail())
            <div class="alert alert-warning mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>Email kamu belum diverifikasi. Verifikasi dulu untuk bisa menantang atau bertanding.</span>
                <a href="{{ route('verification.notice') }}" class="btn btn-sm btn-dark">Verifikasi Sekarang</a>
            </div>
        @endif
        <div class="text-center hero">
            <div class="mb-3"><x-logo :size="96" /></div>
            <h1 class="hero-title">JUARACATUR</h1>
            <p class="lead text-white-50">Tantang pemain lain, pertaruhkan poin, dan buktikan siapa raja catur.</p>
            <div class="mt-4">
                @auth
                    <a href="{{ route('rooms.index') }}" class="btn btn-danger btn-lg px-4 fw-bold text-uppercase">Room Tantangan</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-danger btn-lg px-4 me-2 fw-bold text-uppercase">Daftar Sekarang</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 fw-bold text-uppercase">Masuk</a>
                @endauth
            </div>
        </div>

        <div class="row g-4 pb-5">
            <div class="col-md-3">
                <div class="feature-card p-4 h-100">
                    <div class="fs-2 mb-2">🎮</div>
                    <h5>Tantang Pemain di Sekitarmu</h5>
                    <p class="text-white-50 small mb-0">Tantang pemain siapapun atau pemain tertentu, tentukan taruhan poin.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card p-4 h-100">
                    <div class="fs-2 mb-2">📍</div>
                    <h5>Pilih Tempat Bertanding</h5>
                    <p class="text-white-50 small mb-0">Pilih lokasi resmi dari daftar tempat yang dikelola admin.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card p-4 h-100">
                    <div class="fs-2 mb-2">🏆</div>
                    <h5>Sistem Poin &amp; Hadiah</h5>
                    <p class="text-white-50 small mb-0">Menang duel, ambil poin taruhan lawan. Poin awal 1000 setiap daftar.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card p-4 h-100">
                    <div class="fs-2 mb-2">📋</div>
                    <h5>Turnamen</h5>
                    <p class="text-white-50 small mb-0">Ikuti turnamen round robin yang dibuat oleh admin.</p>
                </div>
            </div>
        </div>

        @php
            $detailRoute = fn ($name, $params = []) => auth()->check() ? route($name, $params) : route('register');
        @endphp

        <div class="row g-4 pb-4">
            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">🗓️ Turnamen Akan Datang</h5>
                        <a href="{{ $detailRoute('tournaments.index', ['status' => 'upcoming']) }}" class="small text-white-50">Lihat Semua &rarr;</a>
                    </div>
                    @forelse ($upcomingTournaments as $tournament)
                        <a href="{{ $detailRoute('tournaments.show', $tournament) }}" class="list-row">
                            @if ($tournament->flyerUrl())
                                <img src="{{ $tournament->flyerUrl() }}" alt="" class="rounded me-3 flex-shrink-0" style="width: 44px; height: 56px; object-fit: cover;">
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $tournament->name }}</div>
                                <div class="small text-white-50">{{ $tournament->participants_count }} peserta</div>
                            </div>
                            @if ($tournament->starts_at)
                                <span class="badge bg-danger text-nowrap ms-2">{{ $tournament->starts_at->translatedFormat('d M, H:i') }}</span>
                            @endif
                        </a>
                    @empty
                        <p class="text-white-50 small mb-0">Belum ada turnamen yang akan datang.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">🏆 Turnamen Sedang Berlangsung</h5>
                        <a href="{{ $detailRoute('tournaments.index', ['status' => 'ongoing']) }}" class="small text-white-50">Lihat Semua &rarr;</a>
                    </div>
                    @forelse ($ongoingTournaments as $tournament)
                        <a href="{{ $detailRoute('tournaments.show', $tournament) }}" class="list-row">
                            @if ($tournament->flyerUrl())
                                <img src="{{ $tournament->flyerUrl() }}" alt="" class="rounded me-3 flex-shrink-0" style="width: 44px; height: 56px; object-fit: cover;">
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $tournament->name }}</div>
                                <div class="small text-white-50">{{ $tournament->participants_count }} peserta</div>
                            </div>
                            <span class="badge bg-primary">Berjalan</span>
                        </a>
                    @empty
                        <p class="text-white-50 small mb-0">Tidak ada turnamen yang sedang berlangsung.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">⚔️ Penantang Mencari Lawan</h5>
                        <a href="{{ $detailRoute('rooms.index') }}" class="small text-white-50">Lihat Semua &rarr;</a>
                    </div>
                    @forelse ($openChallengers as $room)
                        <a href="{{ $detailRoute('rooms.show', $room) }}" class="list-row">
                            <div>
                                <div class="fw-semibold"><span class="blink-dot"></span>{{ $room->challenger->name }}</div>
                                <div class="small text-white-50">
                                    {{ $room->venue->name }}
                                    @if ($room->opponent)
                                        &middot; menantang {{ $room->opponent->name }}
                                    @else
                                        &middot; terbuka untuk siapapun
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-success text-nowrap ms-2">{{ number_format($room->wager_points) }} poin</span>
                        </a>
                    @empty
                        <p class="text-white-50 small mb-0">Belum ada penantang terbuka saat ini.</p>
                    @endforelse
                    @guest
                        <div class="mt-3">
                            <a href="{{ route('register') }}" class="btn btn-sm btn-danger">Daftar untuk Menerima Tantangan</a>
                        </div>
                    @endguest
                </div>
            </div>

            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">🔴 Live Room</h5>
                        <span class="small text-white-50">Sedang bertanding</span>
                    </div>
                    @forelse ($liveRooms as $room)
                        <a href="{{ $detailRoute('rooms.show', $room) }}" class="list-row">
                            <div>
                                <div class="fw-semibold"><span class="blink-dot"></span>{{ $room->challenger->name }} vs {{ $room->opponent?->name ?? '-' }}</div>
                                <div class="small text-white-50">{{ $room->venue?->name ?? 'Tempat belum dipilih' }}</div>
                            </div>
                            <span class="badge bg-danger text-nowrap ms-2">{{ number_format($room->wager_points) }} poin</span>
                        </a>
                    @empty
                        <p class="text-white-50 small mb-0">Belum ada pertandingan yang sedang berlangsung.</p>
                    @endforelse
                </div>
            </div>


            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">🆕 Pemain Baru Bergabung</h5>
                        <a href="{{ $detailRoute('players.index') }}" class="small text-white-50">Lihat Semua &rarr;</a>
                    </div>
                    @forelse ($newParticipants as $player)
                        <a href="{{ $detailRoute('players.show', $player) }}" class="list-row">
                            <div class="d-flex align-items-start gap-3">
                                <x-player-avatar :user="$player" :size="44" />
                                <div>
                                    <div class="fw-semibold">{{ $player->name }}</div>
                                    <x-player-meta :user="$player" class="small text-white-50" />
                                </div>
                            </div>
                            <span class="small text-white-50 text-nowrap">{{ $player->created_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <p class="text-white-50 small mb-0">Belum ada pemain baru.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">🥇 Ranking Teratas</h5>
                        <a href="{{ $detailRoute('leaderboard.index') }}" class="small text-white-50">Lihat Semua &rarr;</a>
                    </div>
                    @forelse ($topRanking as $i => $player)
                        <a href="{{ $detailRoute('players.show', $player) }}" class="list-row">
                            <div class="d-flex align-items-start gap-3">
                                <x-player-avatar :user="$player" :size="44" />
                                <div>
                                    <div class="fw-semibold">{{ $i + 1 }}. {{ $player->name }}</div>
                                    <div class="small text-white-50">{{ $player->wins }} menang dari {{ $player->total_matches }} match</div>
                                    <x-player-meta :user="$player" class="small text-white-50" />
                                </div>
                            </div>
                            <span class="badge bg-success text-nowrap ms-2">{{ number_format($player->winrate * 100, 1) }}% WR</span>
                        </a>
                    @empty
                        <p class="text-white-50 small mb-0">Belum ada pemain yang menyelesaikan pertandingan.</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">📍 Tempat Bertanding Aktif</h5>
                        <a href="{{ $detailRoute('venues.index') }}" class="small text-white-50">Lihat Semua &rarr;</a>
                    </div>
                    @forelse ($activeVenues as $venue)
                        <div class="list-row">
                            <div>
                                <div class="fw-semibold">{{ $venue->name }}</div>
                                <div class="small text-white-50">{{ $venue->address }}</div>
                            </div>
                            <span class="badge bg-primary text-nowrap ms-2">{{ $venue->ongoing_tournaments_count }} turnamen</span>
                        </div>
                    @empty
                        <p class="text-white-50 small mb-0">Belum ada tempat yang sedang mengadakan turnamen.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <p class="text-center text-white-50 small pb-4">Catatan: penantang wajib membawa papan catur sendiri saat bertanding.</p>
    </div>
    @auth
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        @include('layouts.partials.notifications-poll')
    @endauth
</body>
</html>
