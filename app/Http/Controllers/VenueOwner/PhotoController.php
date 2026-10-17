<?php

namespace App\Http\Controllers\VenueOwner;

use App\Http\Controllers\Controller;
use App\Models\VenuePhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    private const MAX_PHOTOS = 10;

    public function store(Request $request): RedirectResponse
    {
        $venue = DashboardController::venueFor($request);

        $request->validate([
            'photos' => ['required', 'array', 'max:'.self::MAX_PHOTOS],
            'photos.*' => ['image', 'max:4096'],
        ], [
            'photos.*.image' => 'Setiap file harus berupa gambar (jpg, png, atau webp).',
            'photos.*.max' => 'Ukuran setiap foto maksimal 4MB.',
        ]);

        $remaining = self::MAX_PHOTOS - $venue->photos()->count();
        $files = $request->file('photos');

        if (count($files) > $remaining) {
            return back()->with('error', "Galeri maksimal ".self::MAX_PHOTOS." foto. Sisa slot: {$remaining}.");
        }

        foreach ($files as $file) {
            $path = $file->store('venues/'.$venue->id, 'public');
            $venue->photos()->create(['path' => $path]);
        }

        return back()->with('success', count($files).' foto ditambahkan ke galeri.');
    }

    public function destroy(Request $request, VenuePhoto $photo): RedirectResponse
    {
        $venue = DashboardController::venueFor($request);
        abort_unless($photo->venue_id === $venue->id, 403);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Foto dihapus dari galeri.');
    }
}
