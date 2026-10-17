<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\TournamentParticipant;
use App\Models\User;
use App\Notifications\PointsAdjusted;
use App\Services\PointLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $sortable = [
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'status' => 'is_active',
            'points' => 'points',
        ];

        $sort = array_key_exists($request->query('sort'), $sortable) ? $request->query('sort') : 'points';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $query = User::query();

        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }

        $users = $query->orderBy($sortable[$sort], $dir)->orderBy('id')->get();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $request->query('q'),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function destroyPhoto(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak bisa diubah dari sini.');

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }

        return back()->with('success', 'Foto profil pemain sudah dihapus.');
    }

    public function show(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        $rooms = Room::query()
            ->where(fn ($q) => $q->where('challenger_id', $user->id)->orWhere('opponent_id', $user->id))
            ->with(['challenger', 'opponent', 'venue', 'winner'])
            ->latest()
            ->limit(20)
            ->get();

        $tournaments = TournamentParticipant::where('user_id', $user->id)->with('tournament')->get();

        $transactions = $user->pointTransactions()->latest()->limit(20)->get();

        $redemptions = $user->redemptions()->with('item')->latest()->limit(20)->get();

        return view('admin.users.show', [
            'player' => $user,
            'wins' => $user->wonRooms()->count(),
            'totalMatches' => $user->completedRoomsCount(),
            'rooms' => $rooms,
            'tournaments' => $tournaments,
            'transactions' => $transactions,
            'redemptions' => $redemptions,
        ]);
    }

    public function edit(User $user): View
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak bisa diubah dari sini.');

        return view('admin.users.edit', ['player' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak bisa diubah dari sini.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'after:1900-01-01', 'before:today'],
            'role' => ['required', Rule::in(['player', 'vendor'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'Data pemain berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak bisa dihapus.');
        abort_if($user->id === $request->user()->id, 403, 'Kamu tidak bisa menghapus akunmu sendiri.');

        if ($user->hasHistory()) {
            $user->update(['is_active' => false]);

            return redirect()->route('admin.users.index')->with('success', "{$user->name} memiliki riwayat permainan/poin, jadi akunnya dinonaktifkan (tidak dihapus permanen).");
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->notifications()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pemain berhasil dihapus permanen.');
    }

    public function adjustPoints(Request $request, User $user, PointLedger $ledger): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'not_in:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['amount'] < 0 && $user->points + $data['amount'] < 0) {
            return back()->with('error', 'Poin pemain tidak bisa menjadi negatif.');
        }

        $ledger->record($user, $data['amount'], 'admin_adjustment', description: $data['description'] ?? 'Penyesuaian oleh admin');

        $user->notify(new PointsAdjusted($data['amount'], $data['description'] ?? null));

        return back()->with('success', "Poin {$user->name} berhasil disesuaikan.");
    }

    public function activity(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        return view('admin.users.activity', [
            'player' => $user,
            'transactions' => $user->pointTransactions()->with('room')->latest('id')->paginate(30, ['*'], 'poin'),
            'notifications' => $user->notifications()->latest()->paginate(30, ['*'], 'notif'),
        ]);
    }
}
