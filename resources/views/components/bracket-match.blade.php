@props(['match'])

@php
    $p1Win = $match->status === 'completed' && $match->winner_id !== null && $match->winner_id === $match->player1_id;
    $p2Win = $match->status === 'completed' && $match->winner_id !== null && $match->winner_id === $match->player2_id;
@endphp

<div class="bracket-match">
    <div class="bracket-slot {{ $p1Win ? 'win' : '' }} {{ $match->player1 ? '' : 'empty' }}">{{ $match->player1->name ?? 'Menunggu pemenang' }}</div>
    <div class="bracket-slot {{ $p2Win ? 'win' : '' }} {{ $match->player2 ? '' : 'empty' }}">{{ $match->player2->name ?? 'Menunggu pemenang' }}</div>
    <div class="bracket-foot">
        @if ($match->status === 'completed')
            {{ $match->winner ? 'Menang: '.$match->winner->name : 'Seri' }}
        @elseif ($match->status === 'disputed')
            Sengketa, menunggu admin
        @elseif ($match->isReadyToPlay())
            Belum main
        @else
            Menunggu
        @endif
        @if ($match->scheduled_at)
            &middot; {{ $match->scheduled_at->translatedFormat('d M, H:i') }}
        @endif
    </div>
</div>
