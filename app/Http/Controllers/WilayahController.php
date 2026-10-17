<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function children(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent' => ['required', 'string', 'max:13'],
        ]);

        return response()->json(
            Wilayah::where('parent_kode', $data['parent'])->orderBy('kode')->get(['kode', 'nama'])
        );
    }
}
