@extends('layouts.app')

@section('title', 'Riwayat Penukaran Poin')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Riwayat Penukaran Poin</h4>
            <div class="small text-muted">Semua penukaran barang vendor dan voucher admin.</div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark">Kembali ke Dashboard</a>
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

    <form method="GET" action="{{ route('admin.redemptions.index') }}" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Cari nama pemain, barang, atau kode voucher..." value="{{ $q }}">
        </div>
        <div class="col-md-3">
            <select name="tipe" class="form-select" aria-label="Filter jenis">
                <option value="">Semua jenis</option>
                <option value="barang" @selected($tipe === 'barang')>Barang vendor</option>
                <option value="voucher" @selected($tipe === 'voucher')>Voucher admin</option>
                <option value="voucher_ditukar" @selected($tipe === 'voucher_ditukar')>Voucher sudah ditukar</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="menunggu" @selected($status === 'menunggu')>Menunggu</option>
                <option value="selesai" @selected($status === 'selesai')>Selesai</option>
            </select>
        </div>
        <div class="col-md-auto">
            <button class="btn btn-dark">Terapkan</button>
            @if ($q || $tipe || $status)
                <a href="{{ route('admin.redemptions.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Waktu</th><th>Pemain</th><th>Jenis</th><th>Barang / Voucher</th><th>Vendor</th><th>Poin</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($redemptions as $r)
                        <tr>
                            <td class="small text-muted text-nowrap">{{ $r->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ $r->user->name }}<div class="small text-muted">{{ $r->user->email }}</div></td>
                            <td><span class="badge bg-{{ $r->item?->type === 'voucher' ? 'info text-dark' : 'light text-dark' }}">{{ $r->item?->type === 'voucher' ? 'Voucher' : 'Barang' }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $r->item_name }}</div>
                                @if ($r->voucher_code)
                                    <div class="small font-monospace">{{ $r->voucher_code }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $r->vendor?->name ?? 'Admin' }}</td>
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
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Belum ada penukaran yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $redemptions->links() }}</div>
@endsection
