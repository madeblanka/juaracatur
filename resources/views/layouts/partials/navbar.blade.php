    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-danger border-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing') }}">
                <x-logo :size="34" />
                <span class="brand-word">JUARACATUR</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('rooms.*') ? 'active' : '' }}" href="{{ route('rooms.index') }}">Room</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('tournaments.*') ? 'active' : '' }}" href="{{ route('tournaments.index') }}">Turnamen</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('leaderboard.*') ? 'active' : '' }}" href="{{ route('leaderboard.index') }}">Leaderboard</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('players.*') ? 'active' : '' }}" href="{{ route('players.index') }}">Pemain</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('venues.*') ? 'active' : '' }}" href="{{ route('venues.index') }}">Venue</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap {{ request()->routeIs('exchange.*') ? 'active' : '' }}" href="{{ route('exchange.index') }}">Tukar Poin</a></li>
                    @if (auth()->user()?->isAdmin())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-nowrap text-warning" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kelola Admin</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard Admin</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Pemain</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.tournaments.index') }}">Turnamen</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.vouchers.index') }}">Voucher</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.redemptions.index') }}">Riwayat Penukaran</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.rooms.index') }}">Sengketa Room</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.venues.index') }}">Kelola Venue</a></li>
                            </ul>
                        </li>
                    @elseif (auth()->user()?->isVendor())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-nowrap text-info" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kelola Toko</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('vendor.items.index') }}">Barang Saya</a></li>
                                <li><a class="dropdown-item" href="{{ route('vendor.redemptions.index') }}">Riwayat Penukaran</a></li>
                                <li><a class="dropdown-item" href="{{ route('vendor.tournaments.index') }}">Turnamen Saya</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item me-3">
                        <a href="{{ route('points.history') }}" class="badge bg-success points-badge text-decoration-none" title="Riwayat poin"><i class="bi bi-coin"></i> {{ number_format(auth()->user()->points) }} poin</a>
                    </li>
                    @php $unreadNotifications = auth()->user()->unreadNotifications; @endphp
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" @if ($unreadNotifications->count() === 0) style="display:none;" @endif>{{ $unreadNotifications->count() }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                            <div id="notif-items">
                                @forelse ($unreadNotifications->take(5) as $notification)
                                    <li>
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item small text-wrap">{{ $notification->data['message'] ?? '' }}</button>
                                        </form>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item-text small text-muted" id="notif-empty">Tidak ada notifikasi baru.</span></li>
                                @endforelse
                            </div>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="{{ route('notifications.index') }}">Lihat semua notifikasi</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil Saya</a></li>
                            <li><a class="dropdown-item" href="{{ route('points.history') }}">Riwayat Poin</a></li>
                            <li><a class="dropdown-item" href="{{ route('settings.edit') }}">Pengaturan Akun</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item">Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
