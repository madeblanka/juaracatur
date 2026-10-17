@extends('layouts.app')

@section('title', 'Tukar Poin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Tukar Poin</h4>
            <span class="small text-muted">Poinmu saat ini: <strong>{{ number_format(auth()->user()->points) }}</strong></span>
        </div>
    </div>

    @php
        $barang = $items->where('type', 'barang');
        $vouchers = $items->where('type', 'voucher');
    @endphp

    <h6 class="text-muted mb-3">Barang dari Vendor</h6>
    <div class="row g-3 mb-5">
        @forelse ($barang as $item)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold mb-1">{{ $item->name }}</h6>
                        <p class="small text-muted mb-1">Vendor: {{ $item->vendor->name }}</p>
                        @if ($item->description)
                            <p class="small mb-2">{{ $item->description }}</p>
                        @endif
                        <div class="mt-auto">
                            <span class="badge bg-success mb-2"><i class="bi bi-coin"></i> {{ number_format($item->point_cost) }} poin</span>
                            @if ($item->stock !== null)
                                <span class="badge bg-light text-dark mb-2">Stok {{ $item->stock }}</span>
                            @endif
                            <form method="POST" action="{{ route('exchange.redeem', $item) }}" onsubmit="return confirm('Tukar {{ number_format($item->point_cost) }} poin untuk barang ini?');">
                                @csrf
                                <button class="btn btn-dark btn-sm w-100" @disabled(auth()->user()->points < $item->point_cost)>
                                    {{ auth()->user()->points < $item->point_cost ? 'Poin tidak cukup' : 'Tukar Sekarang' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Belum ada barang yang tersedia untuk ditukar.</div>
        @endforelse
    </div>

    <h6 class="text-muted mb-3">Voucher</h6>
    <div class="row g-3 mb-5">
        @forelse ($vouchers as $item)
            <div class="col-md-4">
                <div class="card h-100 border-warning">
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold mb-1">{{ $item->name }}</h6>
                        @if ($item->description)
                            <p class="small mb-2">{{ $item->description }}</p>
                        @endif
                        <div class="mt-auto">
                            <span class="badge bg-success mb-2"><i class="bi bi-coin"></i> {{ number_format($item->point_cost) }} poin</span>
                            @if ($item->stock !== null)
                                <span class="badge bg-light text-dark mb-2">Sisa {{ $item->stock }}</span>
                            @endif
                            @if ($item->expires_at)
                                <span class="badge bg-light text-dark mb-2">Berlaku s.d. {{ $item->expires_at->translatedFormat('d M Y') }}</span>
                            @endif
                            <form method="POST" action="{{ route('exchange.redeem', $item) }}" onsubmit="return confirm('Tukar {{ number_format($item->point_cost) }} poin untuk voucher ini?');">
                                @csrf
                                <button class="btn btn-warning btn-sm w-100" @disabled(auth()->user()->points < $item->point_cost)>
                                    {{ auth()->user()->points < $item->point_cost ? 'Poin tidak cukup' : 'Dapatkan Voucher' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Belum ada voucher tersedia.</div>
        @endforelse
    </div>

    <h6 class="text-muted mb-3">Riwayat Penukaran Saya</h6>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Barang / Voucher</th><th>Poin</th><th>Status</th><th>Kode Voucher</th></tr></thead>
                <tbody>
                    @forelse ($redemptions as $r)
                        <tr>
                            <td class="fw-semibold">{{ $r->item_name }}</td>
                            <td>{{ number_format($r->point_cost) }}</td>
                            <td>
                                @if ($r->status === 'selesai')
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-warning text-dark">Menunggu vendor</span>
                                @endif
                            </td>
                            <td>@if ($r->voucher_code)<code>{{ $r->voucher_code }}</code>@else<span class="text-muted">-</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">Belum ada penukaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
