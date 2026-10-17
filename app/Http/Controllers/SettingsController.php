<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $kode = $user->kode_wilayah;

        return view('settings.edit', [
            'user' => $user,
            'provinsi' => Wilayah::whereNull('parent_kode')->orderBy('kode')->get(['kode', 'nama']),
            'kabupatenOptions' => $kode ? Wilayah::where('parent_kode', substr($kode, 0, 2))->orderBy('kode')->get(['kode', 'nama']) : collect(),
            'kecamatanOptions' => $kode ? Wilayah::where('parent_kode', substr($kode, 0, 5))->orderBy('kode')->get(['kode', 'nama']) : collect(),
            'kelurahanOptions' => $kode ? Wilayah::where('parent_kode', substr($kode, 0, 8))->orderBy('kode')->get(['kode', 'nama']) : collect(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'after:1900-01-01', 'before:today'],
            'kode_wilayah' => ['nullable', 'string', Rule::exists('wilayah', 'kode')->where(fn ($q) => $q->whereRaw('LENGTH(kode) = 13'))],
            'photo' => [$user->photo ? 'nullable' : 'required', 'image', 'max:2048'],
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'birthdate.before' => 'Tanggal lahir harus sebelum hari ini.',
            'kode_wilayah.exists' => 'Pilih kelurahan/desa yang valid.',
            'photo.image' => 'File harus berupa gambar (jpg, png, atau webp).',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'photo.required' => 'Foto profil wajib diunggah.',
        ]);

        $kodeWilayah = $data['kode_wilayah'] ?? null;

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'kode_wilayah' => $kodeWilayah,
            'wilayah' => $kodeWilayah ? Wilayah::kabupatenNama($kodeWilayah) : null,
        ];

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $updates['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $user->update($updates);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
