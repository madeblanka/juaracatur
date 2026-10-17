<?php

namespace App\Http\Controllers\VenueOwner;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $venue = $this->venueFor($request);

        return view('venue-owner.dashboard', [
            'venue' => $venue,
            'photos' => $venue->photos()->latest('id')->get(),
            'lobby' => $this->lobbyFor($venue),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $venue = $this->venueFor($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lokasi_google_maps' => ['required', 'string', 'max:500'],
        ]);

        [$latitude, $longitude] = Venue::parseLokasiGoogleMaps($data['lokasi_google_maps']);

        $venue->update([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'description' => $data['description'] ?? null,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        return back()->with('success', 'Profil dan lokasi tempat berhasil diperbarui.');
    }

    public static function venueFor(Request $request): Venue
    {
        $venue = $request->user()->venue;

        abort_if($venue === null, 403, 'Akun ini belum terhubung ke tempat.');

        return $venue;
    }

    public static function lobbyFor(Venue $venue)
    {
        return Room::query()
            ->where('venue_id', $venue->id)
            ->whereIn('status', ['open', 'accepted'])
            ->with(['challenger', 'opponent'])
            ->latest()
            ->get();
    }
}
