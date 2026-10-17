@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
    <h4 class="fw-bold mb-4">🏆 Leaderboard</h4>

    @php
        $sortLink = fn (string $key) => route('leaderboard.index', [
            'sort' => $key,
            'dir' => $sort === $key && $dir === 'desc' ? 'asc' : 'desc',
        ]);
        $sortIcon = fn (string $key) => $sort === $key ? ($dir === 'asc' ? ' <i class="bi bi-arrow-up"></i>' : ' <i class="bi bi-arrow-down"></i>') : '';
    @endphp

    <div class="card">
        <div class="table-responsive"><table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th><a href="{{ $sortLink('pemain') }}" class="text-reset text-decoration-none">Pemain{!! $sortIcon('pemain') !!}</a></th>
                    <th><a href="{{ $sortLink('poin') }}" class="text-reset text-decoration-none">Poin{!! $sortIcon('poin') !!}</a></th>
                    <th><a href="{{ $sortLink('menang') }}" class="text-reset text-decoration-none">Menang{!! $sortIcon('menang') !!}</a></th>
                    <th><a href="{{ $sortLink('kalah') }}" class="text-reset text-decoration-none">Kalah{!! $sortIcon('kalah') !!}</a></th>
                    <th><a href="{{ $sortLink('total') }}" class="text-reset text-decoration-none">Total Main{!! $sortIcon('total') !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $i => $player)
                    @php $totalMatches = $player->challenged_completed + $player->joined_completed; @endphp
                    <tr>
                        <td class="fw-bold">{{ $players->firstItem() + $i }}</td>
                        <td>
                            <div class="d-flex align-items-start gap-3">
                                <x-player-avatar :user="$player" :size="44" />
                                <div>
                                    <a href="{{ route('players.show', $player) }}" class="text-decoration-none fw-semibold">{{ $player->name }}</a>
                                    <x-player-meta :user="$player" />
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold text-success"><x-points-link :user="$player" /></td>
                        <td>{{ $player->wins }}</td>
                        <td>{{ $totalMatches - $player->wins }}</td>
                        <td>{{ $totalMatches }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Belum ada pemain.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    <div class="mt-3">{{ $players->links() }}</div>
@endsection
