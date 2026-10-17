<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ExchangeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $items = ExchangeItem::where('vendor_id', $request->user()->id)
            ->where('type', 'barang')
            ->withCount('redemptions')
            ->latest()
            ->get();

        return view('vendor.items.index', ['items' => $items]);
    }

    public function create(): View
    {
        return view('vendor.items.form', ['item' => new ExchangeItem(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        ExchangeItem::create($data + [
            'vendor_id' => $request->user()->id,
            'type' => 'barang',
        ]);

        return redirect()->route('vendor.items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Request $request, ExchangeItem $item): View
    {
        $this->ensureOwner($request, $item);

        return view('vendor.items.form', ['item' => $item]);
    }

    public function update(Request $request, ExchangeItem $item): RedirectResponse
    {
        $this->ensureOwner($request, $item);

        $item->update($this->validated($request));

        return redirect()->route('vendor.items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Request $request, ExchangeItem $item): RedirectResponse
    {
        $this->ensureOwner($request, $item);

        if ($item->redemptions()->exists()) {
            $item->update(['is_active' => false]);

            return back()->with('success', 'Barang sudah pernah ditukar, jadi tidak dihapus permanen. Barang dinonaktifkan.');
        }

        $item->delete();

        return redirect()->route('vendor.items.index')->with('success', 'Barang berhasil dihapus.');
    }

    private function ensureOwner(Request $request, ExchangeItem $item): void
    {
        abort_unless($item->vendor_id === $request->user()->id && $item->type === 'barang', 403);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'point_cost' => ['required', 'integer', 'min:1', 'max:1000000'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'point_cost' => $data['point_cost'],
            'stock' => $data['stock'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
