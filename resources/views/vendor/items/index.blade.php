@extends('layouts.app')

@section('title', 'Barang Vendor')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Barang Tukar Poin</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.redemptions.index') }}" class="btn btn-outline-dark">Penukaran Masuk</a>
            <a href="{{ route('vendor.items.create') }}" class="btn btn-dark">+ Tambah Barang</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Barang</th><th>Nilai (poin)</th><th>Stok</th><th>Ditukar</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td>{{ number_format($item->point_cost) }}</td>
                            <td>{{ $item->stock ?? 'Tidak terbatas' }}</td>
                            <td>{{ $item->redemptions_count }}</td>
                            <td><span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-nowrap">
                                <a href="{{ route('vendor.items.edit', $item) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                <form method="POST" action="{{ route('vendor.items.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus barang ini?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Belum ada barang. Tambahkan barang pertamamu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
