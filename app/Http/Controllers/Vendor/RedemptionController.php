<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Notifications\RedemptionCompleted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedemptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['menunggu', 'selesai'], true) ? $request->query('status') : null;

        $base = Redemption::where('vendor_id', $request->user()->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->query('q');
                $query->where(fn ($q) => $q->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")));
            });

        $summary = [
            'total' => (clone $base)->count(),
            'poin' => (int) (clone $base)->sum('point_cost'),
            'menunggu' => (clone $base)->where('status', 'menunggu')->count(),
        ];

        $redemptions = (clone $base)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with('user')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('vendor.redemptions.index', [
            'redemptions' => $redemptions,
            'summary' => $summary,
            'status' => $status,
            'q' => $request->query('q'),
        ]);
    }

    public function complete(Request $request, Redemption $redemption): RedirectResponse
    {
        abort_unless($redemption->vendor_id === $request->user()->id, 403);

        if ($redemption->status === 'menunggu') {
            $redemption->update(['status' => 'selesai', 'completed_at' => now()]);
            $redemption->user->notify(new RedemptionCompleted($redemption));
        }

        return back()->with('success', 'Penukaran ditandai selesai.');
    }
}
