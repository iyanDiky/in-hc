<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasuk;

class SuratMasukController extends Controller
{
    public function datatables(Request $request)
    {
        $query = SuratMasuk::query()->withExists('disposisi');

        // Count total records without filtering
        $recordsTotal = $query->count();

        // Apply global search if present
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $search = strtolower($searchValue);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nomor_surat) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(pengirim) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"]);
            });
        }

        // Count filtered records
        $recordsFiltered = $query->count();

        // Apply sorting
        $order = $request->input('order');
        $columns = $request->input('columns');
        if (!empty($order) && !empty($columns)) {
            foreach ($order as $o) {
                $columnIndex = $o['column'];
                $dir = $o['dir'];
                $columnName = $columns[$columnIndex]['data'];
                if ($columnName && $columnName !== 'null') {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->orderBy('tanggal_surat', 'desc');
        }

        // Apply pagination (offset and limit)
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

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
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = date('YmdHis') . '-' . \Illuminate\Support\Facades\Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        }

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
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = date('YmdHis') . '-' . \Illuminate\Support\Facades\Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        } else {
            // Keep the old evidence if no new file is uploaded
            unset($validated['evidence']);
        }

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
