<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(): View
    {
        $vouchers = ExchangeItem::where('type', 'voucher')->withCount('redemptions')->latest()->get();

        return view('admin.vouchers.index', ['vouchers' => $vouchers]);
    }

    public function create(): View
    {
        return view('admin.vouchers.form', ['voucher' => new ExchangeItem(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ExchangeItem::create($this->validated($request) + ['type' => 'voucher']);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function edit(ExchangeItem $voucher): View
    {
        abort_unless($voucher->isVoucher(), 404);

        return view('admin.vouchers.form', ['voucher' => $voucher]);
    }

    public function update(Request $request, ExchangeItem $voucher): RedirectResponse
    {
        abort_unless($voucher->isVoucher(), 404);

        $voucher->update($this->validated($request));

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(ExchangeItem $voucher): RedirectResponse
    {
        abort_unless($voucher->isVoucher(), 404);

        if ($voucher->redemptions()->exists()) {
            $voucher->update(['is_active' => false]);

            return back()->with('success', 'Voucher sudah pernah ditukar, jadi tidak dihapus permanen. Voucher dinonaktifkan.');
        }

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'point_cost' => ['required', 'integer', 'min:1', 'max:1000000'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'point_cost' => $data['point_cost'],
            'stock' => $data['stock'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
