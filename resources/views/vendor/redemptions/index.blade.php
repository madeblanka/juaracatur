@extends('layouts.app')

@section('title', 'Riwayat Penukaran')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Riwayat Penukaran</h4>
            <div class="small text-muted">Penukaran barang milikmu oleh pemain.</div>
        </div>
        <a href="{{ route('vendor.items.index') }}" class="btn btn-outline-dark">Kelola Barang</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold">{{ number_format($summary['total']) }}</div><div class="small text-muted">Total penukaran</div></div></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold text-success">{{ number_format($summary['poin']) }}</div><div class="small text-muted">Total poin ditukar</div></div></div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card text-center"><div class="card-body"><div class="fs-3 fw-bold text-warning">{{ number_format($summary['menunggu']) }}</div><div class="small text-muted">Menunggu diproses</div></div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('vendor.redemptions.index') }}" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Cari nama barang atau nama pemain..." value="{{ $q }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="menunggu" @selected($status === 'menunggu')>Menunggu</option>
                <option value="selesai" @selected($status === 'selesai')>Selesai</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button class="btn btn-dark">Terapkan</button>
            @if ($q || $status)
                <a href="{{ route('vendor.redemptions.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Waktu</th><th>Pemain</th><th>Barang</th><th>Poin</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($redemptions as $r)
                        <tr>
                            <td class="small text-muted text-nowrap">{{ $r->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ $r->user->name }}<div class="small text-muted">{{ $r->user->phone ?: 'No HP belum diisi' }}</div></td>
                            <td class="fw-semibold">{{ $r->item_name }}</td>
                            <td>{{ number_format($r->point_cost) }}</td>
                            <td>
                                @if ($r->status === 'selesai')
                                    <span class="badge bg-success">Selesai</span>
                                    @if ($r->completed_at)
                                        <div class="small text-muted">{{ $r->completed_at->translatedFormat('d M Y H:i') }}</div>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                @endif
                            </td>
                            <td>
                                @if ($r->status === 'menunggu')
                                    <form method="POST" action="{{ route('vendor.redemptions.complete', $r) }}" onsubmit="return confirm('Tandai penukaran ini sudah diproses?');">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Tandai Selesai</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Belum ada penukaran yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $redemptions->links() }}</div>
@endsection
