<?php

namespace App\Http\Controllers;

use App\Models\InstitusiPendidikan;
use Illuminate\Http\Request;

class InstitusiPendidikanController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255']);
        $term = InstitusiPendidikan::nameKey($request->input('search'));
        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        // Literal substring matching: %, _ and backslash are not search wildcards.
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $results = InstitusiPendidikan::whereRaw("nama_normalized LIKE ? ESCAPE '!'", ["%{$escaped}%"])
            ->orderBy('nama_normalized')->limit(10)->get(['id', 'nama']);

        return response()->json(['data' => $results]);
    }
}
