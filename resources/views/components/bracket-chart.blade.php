@props(['tournament'])

@php
    $all = $tournament->matches;
    $isDouble = $tournament->isDoubleElimination();
    $columns = function ($matches, string $bracket) use ($tournament) {
        $maxRound = $matches->max('round') ?? 0;
        return $matches->sortBy('round')->groupBy('round')->map(fn ($group, $round) => [
            'label' => $tournament->roundLabel($bracket, (int) $round, $maxRound),
            'matches' => $group,
        ])->values();
    };
    $winnersCols = $isDouble ? $columns($all->where('bracket', 'winners'), 'winners') : $columns($all, 'winners');
    $losersCols = $isDouble ? $columns($all->where('bracket', 'losers'), 'losers') : collect();
    $grand = $isDouble ? $all->where('bracket', 'grand')->values() : collect();
@endphp

@if ($all->isEmpty())
    <p class="text-muted mb-0">Bagan belum dibuat.</p>
@else
    <style>
        .bracket-scroll { overflow-x: auto; padding-bottom: .5rem; }
        .bracket { display: flex; gap: 2.5rem; align-items: stretch; min-width: max-content; }
        .bracket-round { display: flex; flex-direction: column; width: 220px; }
        .bracket-title { font-size: .75rem; font-weight: 700; color: #6c757d; text-align: center; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .5rem; }
        .bracket-matches { display: flex; flex-direction: column; justify-content: space-around; flex: 1; gap: .75rem; }
        .bracket-match { border: 1px solid #dee2e6; border-radius: .5rem; background: #fff; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
        .bracket-slot { padding: .45rem .65rem; font-size: .9rem; }
        .bracket-slot + .bracket-slot { border-top: 1px solid #eef0f2; }
        .bracket-slot.win { font-weight: 700; background: #e8f7ee; }
        .bracket-slot.empty { color: #9aa3ad; font-style: italic; }
        .bracket-foot { padding: .25rem .65rem; background: #f8f9fa; color: #6c757d; font-size: .75rem; }
        .bracket-section-title { font-weight: 700; font-size: .9rem; margin: .25rem 0 .5rem; }
    </style>

    @if ($isDouble)
        <div class="bracket-section-title">Winners Bracket</div>
    @endif
    <div class="bracket-scroll">
        <div class="bracket">
            @foreach ($winnersCols as $col)
                <div class="bracket-round">
                    <div class="bracket-title">{{ $col['label'] }}</div>
                    <div class="bracket-matches">
                        @foreach ($col['matches'] as $match)
                            <x-bracket-match :match="$match" />
                        @endforeach
                    </div>
                </div>
            @endforeach
            @if ($grand->isNotEmpty())
                @foreach ($grand as $match)
                    <div class="bracket-round">
                        <div class="bracket-title">Grand Final</div>
                        <div class="bracket-matches">
                            <x-bracket-match :match="$match" />
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    @if ($losersCols->isNotEmpty())
        <div class="bracket-section-title mt-4">Lower Bracket</div>
        <div class="bracket-scroll">
            <div class="bracket">
                @foreach ($losersCols as $col)
                    <div class="bracket-round">
                        <div class="bracket-title">{{ $col['label'] }}</div>
                        <div class="bracket-matches">
                            @foreach ($col['matches'] as $match)
                                <x-bracket-match :match="$match" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
