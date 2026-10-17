<?php

namespace App\Http\Controllers;

use App\Models\ExchangeItem;
use App\Models\Redemption;
use App\Models\User;
use App\Notifications\RedemptionPlaced;
use App\Services\PointLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExchangeController extends Controller
{
    public function index(Request $request): View
    {
        $items = ExchangeItem::available()
            ->with('vendor')
            ->orderBy('type')
            ->orderBy('point_cost')
            ->get();

        $redemptions = Redemption::where('user_id', $request->user()->id)
            ->with('item')
            ->latest()
            ->limit(20)
            ->get();

        return view('exchange.index', [
            'items' => $items,
            'redemptions' => $redemptions,
        ]);
    }

    public function redeem(Request $request, ExchangeItem $item, PointLedger $ledger): RedirectResponse
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($user, $item, $ledger) {
            $lockedItem = ExchangeItem::available()->whereKey($item->id)->lockForUpdate()->first();

            if (! $lockedItem) {
                return ['error', 'Barang atau voucher ini sudah tidak tersedia.'];
            }

            $lockedUser = User::whereKey($user->id)->lockForUpdate()->first();

            if ($lockedUser->points < $lockedItem->point_cost) {
                return ['error', 'Poinmu tidak cukup untuk menukar '.($lockedItem->isVoucher() ? 'voucher' : 'barang').' ini.'];
            }

            $ledger->record($lockedUser, -$lockedItem->point_cost, 'redeem', description: "Tukar poin: {$lockedItem->name}");

            if ($lockedItem->stock !== null) {
                $lockedItem->decrement('stock');
            }

            $isVoucher = $lockedItem->isVoucher();

            $redemption = Redemption::create([
                'user_id' => $lockedUser->id,
                'exchange_item_id' => $lockedItem->id,
                'vendor_id' => $lockedItem->vendor_id,
                'item_name' => $lockedItem->name,
                'point_cost' => $lockedItem->point_cost,
                'status' => $isVoucher ? 'selesai' : 'menunggu',
                'voucher_code' => $isVoucher ? $this->uniqueVoucherCode() : null,
                'completed_at' => $isVoucher ? now() : null,
            ]);

            return ['ok', $redemption, $lockedItem];
        });

        if ($result[0] === 'error') {
            return back()->with('error', $result[1]);
        }

        [, $redemption, $item] = $result;

        if ($item->vendor) {
            $item->vendor->notify(new RedemptionPlaced($redemption));
        }

        $message = $redemption->voucher_code
            ? "Voucher berhasil didapat! Kode kamu: {$redemption->voucher_code}"
            : 'Penukaran berhasil. Vendor akan memproses barangmu.';

        return redirect()->route('exchange.index')->with('success', $message);
    }

    private function uniqueVoucherCode(): string
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
        } while (Redemption::where('voucher_code', $code)->exists());

        return $code;
    }
}
