@extends('layouts.app')

@section('title', 'Kelola Tempat Bertanding')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Tempat Bertanding</h4>
        <a href="{{ route('admin.venues.create') }}" class="btn btn-dark">+ Tambah Tempat</a>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="table mb-0 align-middle">
            <thead><tr><th>Nama</th><th>Alamat</th><th>Room</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse ($venues as $venue)
                    <tr>
                        <td class="fw-semibold">{{ $venue->name }}</td>
                        <td class="text-muted small">{{ $venue->address }}</td>
                        <td>{{ $venue->rooms_count }}</td>
                        <td>
                            @if ($venue->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.venues.edit', $venue) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                            <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}" class="d-inline" onsubmit="return confirm('Hapus tempat ini?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">Belum ada tempat bertanding.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
@endsection
