@extends('layouts.app')

@section('title', 'Kelola Turnamen')

@section('content')
    @php $routePrefix = explode('.', request()->route()->getName())[0]; @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Turnamen</h4>
        <a href="{{ route($routePrefix.'.tournaments.create') }}" class="btn btn-dark">+ Buat Turnamen</a>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="table mb-0 align-middle">
            <thead><tr><th>Nama</th><th>Format</th><th>Peserta</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($tournaments as $tournament)
                    <tr>
                        <td class="fw-semibold">{{ $tournament->name }}</td>
                        <td>{{ str_replace('_', ' ', $tournament->format) }}</td>
                        <td>{{ $tournament->participants_count }}</td>
                        <td><span class="badge bg-{{ match($tournament->status) { 'draft' => 'secondary', 'ongoing' => 'primary', 'completed' => 'success', default => 'dark' } }}">{{ strtoupper($tournament->status) }}</span></td>
                        <td class="text-end"><a href="{{ route($routePrefix.'.tournaments.show', $tournament) }}" class="btn btn-sm btn-outline-dark">Kelola</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">Belum ada turnamen.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
@endsection
