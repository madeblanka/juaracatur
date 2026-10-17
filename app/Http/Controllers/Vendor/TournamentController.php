<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets a vendor create and run their own tournaments. Every action that
 * operates on an existing tournament (show, generate, set results, invites,
 * flyer/location/fee edits, ...) is routed straight to Admin\TournamentController
 * - the business logic (bracket building, point ledger, standings) is identical
 * for admin- and vendor-run tournaments, so it lives in one place. The
 * "owns.tournament" route middleware makes sure a vendor can only reach their
 * own tournaments that way; this controller only needs to override the two
 * actions that don't yet have a tournament to scope by ownership.
 */
class TournamentController extends AdminTournamentController
{
    public function index(): View
    {
        $tournaments = Tournament::where('created_by', auth()->id())
            ->withCount('participants')
            ->latest()
            ->get();

        return view('admin.tournaments.index', ['tournaments' => $tournaments]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'format' => ['required', 'in:round_robin,single_elimination,double_elimination'],
            'rr_rounds' => ['nullable', 'integer', 'between:1,5'],
            'flyer' => ['nullable', 'image', 'max:2048'],
            'entry_points' => ['required', 'integer', 'min:0', 'max:100000'],
        ], [
            'flyer.image' => 'File flyer harus berupa gambar (jpg, png, atau webp).',
            'flyer.max' => 'Ukuran flyer maksimal 2MB.',
            'rr_rounds.between' => 'Jumlah putaran round robin harus antara 1 sampai 5.',
        ]);

        $tournament = Tournament::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'flyer' => $request->hasFile('flyer') ? $request->file('flyer')->store('tournaments', 'public') : null,
            'venue_id' => $data['venue_id'] ?? null,
            'format' => $data['format'],
            'rr_rounds' => $data['format'] === 'round_robin' ? ($data['rr_rounds'] ?? 1) : 1,
            'entry_points' => $data['entry_points'],
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('vendor.tournaments.show', $tournament)->with('success', 'Turnamen dibuat. Tambahkan peserta lalu buat bagan.');
    }
}
