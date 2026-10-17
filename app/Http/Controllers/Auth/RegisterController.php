<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'provinsi' => Wilayah::whereNull('parent_kode')->orderBy('kode')->get(['kode', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'birthdate' => ['required', 'date', 'after:1900-01-01', 'before:today'],
            'kode_wilayah' => ['required', 'string', Rule::exists('wilayah', 'kode')->where(fn ($q) => $q->whereRaw('LENGTH(kode) = 13'))],
            'photo' => ['required', 'image', 'max:2048'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'hp_website' => ['prohibited'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'birthdate.before' => 'Tanggal lahir harus sebelum hari ini.',
            'kode_wilayah.exists' => 'Pilih kelurahan/desa yang valid.',
            'photo.image' => 'File harus berupa gambar (jpg, png, atau webp).',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'photo.required' => 'Foto profil wajib diunggah.',
        ]);

        $photoPath = $request->file('photo')->store('avatars', 'public');

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'birthdate' => $data['birthdate'],
                'kode_wilayah' => $data['kode_wilayah'],
                'wilayah' => Wilayah::kabupatenNama($data['kode_wilayah']),
                'photo' => $photoPath,
                'password' => Hash::make($data['password']),
                'role' => 'player',
                'points' => 1000,
            ]);
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return back()->withInput()->withErrors([
                    'email' => 'Email ini sudah terdaftar. Silakan masuk.',
                ]);
            }

            throw $e;
        }

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Selamat datang di Juaracatur! Akunmu sudah aktif dengan 1000 poin awal.');
    }
}
