<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasuk;

class SuratMasukController extends Controller
{
    public function datatables(Request $request)
    {
        $query = SuratMasuk::query()->withExists('disposisi');

        // Total count before any filter
        $recordsTotal = SuratMasuk::count();

        // 0. Tahun Filter
        if ($request->filled('tahun')) {
            $request->validate(['tahun' => 'integer|between:1900,9999']);
            $year = (int) $request->input('tahun');
            $query->whereBetween('tanggal_surat', ["{$year}-01-01", "{$year}-12-31"]);
        }

        // 1. Date Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_surat', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_surat', '<=', $request->input('end_date'));
        }

        // 2. Status Disposisi Filter
        if ($request->filled('status_disposisi')) {
            $status = $request->input('status_disposisi');
            if ($status === 'sudah') {
                $query->has('disposisi');
            } elseif ($status === 'belum') {
                $query->doesntHave('disposisi');
            }
        }

        // 3. Global Search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $search = strtolower($searchValue);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nomor_surat) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(pengirim) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(catatan) LIKE ?', ["%{$search}%"]);
            });
        }

        // Count filtered records
        $recordsFiltered = $query->count();

        // 4. Sorting
        $order = $request->input('order');
        $columns = $request->input('columns');
        if (!empty($order) && !empty($columns)) {
            foreach ($order as $o) {
                $columnIndex = $o['column'];
                $dir = ($o['dir'] ?? '') === 'asc' ? 'asc' : 'desc';
                $columnName = $columns[$columnIndex]['data'] ?? null;
                if ($columnName && $columnName !== 'null' && in_array($columnName, ['tanggal_surat', 'nomor_surat', 'perihal', 'pengirim', 'tujuan', 'created_at'])) {
                    if ($columnName === 'nomor_surat') {
                        $query->orderByRaw("CASE WHEN nomor_surat ~ '^[0-9]+$' THEN CAST(nomor_surat AS BIGINT) END {$dir} NULLS LAST");
                    }
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->orderBy('tanggal_surat', 'desc');
        }
        // Import timestamps can be identical. Resolve date ties using the numeric register number.
        $query->orderBy('tanggal_surat', 'desc')
            ->orderByRaw("CASE WHEN nomor_surat ~ '^[0-9]+$' THEN CAST(nomor_surat AS BIGINT) END DESC NULLS LAST")
            ->orderBy('nomor_surat', 'desc')->orderBy('id', 'desc');

        // 5. Pagination
        $request->validate([
            'start' => 'sometimes|integer|min:0',
            'length' => 'sometimes|integer|min:1|max:100',
        ]);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
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

    public function years()
    {
        $years = SuratMasuk::selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_surat)::integer as year')
            ->whereNotNull('tanggal_surat')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $currentYear = intval(date('Y'));
        if (!in_array($currentYear, $years)) {
            $years[] = $currentYear;
            rsort($years);
        }

        return response()->json($years);
    }

    public function list(Request $request)
    {
        $query = SuratMasuk::query();

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(nomor_surat) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(pengirim) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(catatan) LIKE ?', ["%{$search}%"]);
        }

        $query->orderBy('tanggal_surat', 'desc')
            ->orderByRaw("CASE WHEN nomor_surat ~ '^[0-9]+$' THEN CAST(nomor_surat AS BIGINT) END DESC NULLS LAST")
            ->orderBy('nomor_surat', 'desc')->orderBy('id', 'desc');

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
            'nomor_surat' => 'nullable|string|max:255',
            'pengirim' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:10000',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = date('YmdHis') . '-' . \Illuminate\Support\Facades\Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        }

        $suratMasuk = app(\App\Services\AnnualNumber::class)->create(SuratMasuk::class, 'nomor_surat', 'tanggal_surat', $validated);

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
            'catatan' => 'nullable|string|max:10000',
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
