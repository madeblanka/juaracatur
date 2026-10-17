<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedemptionController extends Controller
{
    public function index(Request $request): View
    {
        $tipeInput = in_array($request->query('tipe'), ['barang', 'voucher', 'voucher_ditukar'], true) ? $request->query('tipe') : null;
        $onlyRedeemedVouchers = $tipeInput === 'voucher_ditukar';
        $type = $onlyRedeemedVouchers ? 'voucher' : $tipeInput;
        $status = $onlyRedeemedVouchers ? 'selesai' : (in_array($request->query('status'), ['menunggu', 'selesai'], true) ? $request->query('status') : null);

        $base = Redemption::query()
            ->when($type, fn ($q) => $q->whereHas('item', fn ($i) => $i->where('type', $type)))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->query('q');
                $query->where(fn ($q) => $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('voucher_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")));
            });

        $summary = [
            'total' => (clone $base)->count(),
            'poin' => (int) (clone $base)->sum('point_cost'),
            'menunggu' => (clone $base)->where('status', 'menunggu')->count(),
        ];

        $redemptions = (clone $base)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['user', 'vendor', 'item'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.redemptions.index', [
            'redemptions' => $redemptions,
            'summary' => $summary,
            'tipe' => $tipeInput,
            'status' => $status,
            'q' => $request->query('q'),
        ]);
    }
}
