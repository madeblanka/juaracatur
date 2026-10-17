<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(): View
    {
        $venues = Venue::withCount('rooms')->orderBy('name')->get();

        return view('admin.venues.index', ['venues' => $venues]);
    }

    public function create(): View
    {
        return view('admin.venues.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'wilayah' => ['nullable', 'string', 'max:255'],
        ]);

        Venue::create($data);

        return redirect()->route('admin.venues.index')->with('success', 'Tempat bertanding berhasil ditambahkan.');
    }

    public function edit(Venue $venue): View
    {
        return view('admin.venues.edit', ['venue' => $venue]);
    }

    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'wilayah' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $venue->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'wilayah' => $data['wilayah'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.venues.index')->with('success', 'Tempat bertanding berhasil diperbarui.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        try {
            $venue->delete();
        } catch (QueryException) {
            return back()->with('error', 'Tempat ini masih dipakai oleh room/turnamen dan tidak bisa dihapus. Nonaktifkan saja.');
        }

        return redirect()->route('admin.venues.index')->with('success', 'Tempat bertanding dihapus.');
    }
}
