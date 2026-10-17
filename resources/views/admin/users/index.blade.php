@extends('layouts.app')

@section('title', 'Kelola Pemain')

@section('content')
    <h4 class="fw-bold mb-4">Pemain</h4>

    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Cari nama, email, atau nomor HP..." value="{{ $q }}">
        </div>
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $dir }}">
        <div class="col-md-auto">
            <button class="btn btn-dark">Cari</button>
            @if ($q)
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    @php
        $sortLink = fn (string $key) => route('admin.users.index', [
            'q' => $q,
            'sort' => $key,
            'dir' => $sort === $key && $dir === 'asc' ? 'desc' : 'asc',
        ]);
        $sortIcon = fn (string $key) => $sort === $key ? ($dir === 'asc' ? ' <i class="bi bi-arrow-up"></i>' : ' <i class="bi bi-arrow-down"></i>') : '';
    @endphp

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('name') }}" class="text-reset text-decoration-none">Nama{!! $sortIcon('name') !!}</a></th>
                        <th><a href="{{ $sortLink('email') }}" class="text-reset text-decoration-none">Email{!! $sortIcon('email') !!}</a></th>
                        <th><a href="{{ $sortLink('role') }}" class="text-reset text-decoration-none">Role{!! $sortIcon('role') !!}</a></th>
                        <th><a href="{{ $sortLink('status') }}" class="text-reset text-decoration-none">Status{!! $sortIcon('status') !!}</a></th>
                        <th><a href="{{ $sortLink('points') }}" class="text-reset text-decoration-none">Poin{!! $sortIcon('points') !!}</a></th>
                        <th>Sesuaikan Poin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="{{ $user->is_active ? '' : 'table-secondary' }}">
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <x-player-avatar :user="$user" :size="36" />
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <x-player-meta :user="$user" />
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small">{{ $user->email }}</td>
                            <td><span class="badge bg-{{ match($user->role) { 'admin' => 'warning text-dark', 'vendor' => 'info text-dark', default => 'light text-dark' } }}">{{ $user->role }}</span></td>
                            <td><span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="fw-bold"><x-points-link :user="$user" /></td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.adjust-points', $user) }}" class="d-flex gap-1">
                                    @csrf
                                    <input type="number" name="amount" class="form-control form-control-sm" placeholder="+/-poin" style="width: 110px" required>
                                    <input type="text" name="description" class="form-control form-control-sm" placeholder="Keterangan" style="width: 160px">
                                    <button class="btn btn-sm btn-outline-dark">Terapkan</button>
                                </form>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-dark">Detail</a>
                                @if ($user->whatsappUrl())
                                    <a href="{{ $user->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-success"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                                @endif
                                @unless ($user->isAdmin())
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                    @if ($user->photo)
                                        <form method="POST" action="{{ route('admin.users.photo.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Hapus foto profil pemain ini?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-warning">Hapus Foto</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Hapus pemain ini? Jika sudah punya riwayat, akun hanya dinonaktifkan.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Tidak ada pemain yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
