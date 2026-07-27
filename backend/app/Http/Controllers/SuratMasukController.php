<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasuk;

class SuratMasukController extends Controller
{
    public function list(Request $request)
    {
        $query = SuratMasuk::query();

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(nomor_surat) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(pengirim) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"]);
        }

        $query->orderBy('tanggal_surat', 'desc');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $suratMasuk = SuratMasuk::findOrFail($request->id);
        
        return response()->json($suratMasuk);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|string|max:255',
            'pengirim' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'evidence' => 'nullable|string|max:255',
            'catatan' => 'nullable|string|max:255',
        ]);

        $suratMasuk = SuratMasuk::create($validated);

        return response()->json(['message' => 'Created successfully', 'data' => $suratMasuk], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $suratMasuk = SuratMasuk::findOrFail($request->id);

        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|string|max:255',
            'pengirim' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'evidence' => 'nullable|string|max:255',
            'catatan' => 'nullable|string|max:255',
        ]);

        $suratMasuk->update($validated);

        return response()->json(['message' => 'Updated successfully', 'data' => $suratMasuk]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $suratMasuk = SuratMasuk::findOrFail($request->id);
        
        $suratMasuk->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
