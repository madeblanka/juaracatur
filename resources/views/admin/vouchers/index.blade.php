@extends('layouts.app')

@section('title', 'Voucher')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Voucher</h4>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-dark">+ Tambah Voucher</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Nama</th><th>Poin</th><th>Sisa Kuota</th><th>Berlaku s.d.</th><th>Ditukar</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($vouchers as $voucher)
                        <tr>
                            <td class="fw-semibold">{{ $voucher->name }}</td>
                            <td>{{ number_format($voucher->point_cost) }}</td>
                            <td>{{ $voucher->stock ?? 'Tidak terbatas' }}</td>
                            <td>{{ $voucher->expires_at?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td>{{ $voucher->redemptions_count }}</td>
                            <td><span class="badge bg-{{ $voucher->is_active ? 'success' : 'secondary' }}">{{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" class="d-inline" onsubmit="return confirm('Hapus voucher ini?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Belum ada voucher.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
