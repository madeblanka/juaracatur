<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class VenueRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.venue-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'venue_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lokasi_google_maps' => ['required', 'string', 'max:500'],
            'hp_website' => ['prohibited'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
        ]);

        [$latitude, $longitude] = Venue::parseLokasiGoogleMaps($data['lokasi_google_maps']);

        $user = DB::transaction(function () use ($data, $latitude, $longitude) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'venue',
            ]);

            Venue::create([
                'owner_id' => $user->id,
                'name' => $data['venue_name'],
                'address' => $data['address'],
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_active' => true,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('venue.dashboard')->with('success', 'Tempat kamu sudah terdaftar. Lengkapi galeri dan lokasi di bawah.');
    }
}
