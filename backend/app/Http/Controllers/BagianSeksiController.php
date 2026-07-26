<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\BagianSeksi;

class BagianSeksiController extends Controller
{
    public function list(Request $request)
    {
        $query = BagianSeksi::with('unitKerja');

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(bagian_seksi) LIKE ?', ["%{$search}%"]);
        }

        $query->orderByRaw('LOWER(bagian_seksi) ASC');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $bagian_seksi = BagianSeksi::with('unitKerja')->findOrFail($request->id);
        
        return response()->json($bagian_seksi);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('bagian_seksi', 'kode')->whereNull('delete_at')],
            'bagian_seksi' => 'required|string|max:255',
            'unit_kerja_id' => 'required|uuid|exists:unit_kerja,id',
        ]);

        $bagian_seksi = BagianSeksi::create($validated);

        return response()->json(['message' => 'Created successfully', 'data' => $bagian_seksi], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $bagian_seksi = BagianSeksi::findOrFail($request->id);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('bagian_seksi', 'kode')->ignore($bagian_seksi->id)->whereNull('delete_at')],
            'bagian_seksi' => 'required|string|max:255',
            'unit_kerja_id' => 'required|uuid|exists:unit_kerja,id',
        ]);

        $bagian_seksi->update($validated);

        return response()->json(['message' => 'Updated successfully', 'data' => $bagian_seksi]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $bagian_seksi = BagianSeksi::findOrFail($request->id);
        
        $bagian_seksi->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
