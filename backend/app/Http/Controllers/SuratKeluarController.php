<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratKeluar;
use Illuminate\Support\Facades\Auth;

class SuratKeluarController extends Controller
{
    public function datatables(Request $request)
    {
        $query = SuratKeluar::query()->with([
            'userInput:id,nama,npp',
            'userRequest:id,nama,npp',
            'bagianSeksiRequest:id,bagian_seksi,kode'
        ]);

        // Total count before any filter
        $recordsTotal = SuratKeluar::count();

        // 0. Tahun Filter
        if ($request->filled('tahun')) {
            $request->validate(['tahun' => 'integer|between:1900,9999']);
            $year = (int) $request->input('tahun');
            $query->where(function ($q) use ($year) {
                $q->whereBetween('tanggal_surat', ["{$year}-01-01", "{$year}-12-31"])
                  ->orWhere(fn ($missing) => $missing->whereNull('tanggal_surat')->where('tahun_arsip', $year));
            });
        }

        // 1. Date Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_surat', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_surat', '<=', $request->input('end_date'));
        }

        // 2. Bagian Seksi Filter
        if ($request->filled('bagian_seksi_request')) {
            $query->where('bagian_seksi_request', $request->input('bagian_seksi_request'));
        }

        // 3. Global Search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $search = strtolower($searchValue);
            $query->where(function($q) use ($search) {
                $q->whereRaw('CAST(nomor_surat AS TEXT) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(catatan) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('userRequest', function($uq) use ($search) {
                      $uq->whereRaw('LOWER(nama) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(npp) LIKE ?', ["%{$search}%"]);
                  })
                  ->orWhereHas('bagianSeksiRequest', function($bq) use ($search) {
                      $bq->whereRaw('LOWER(bagian_seksi) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(kode) LIKE ?', ["%{$search}%"]);
                  });
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
                $dir = $o['dir'];
                $columnName = $columns[$columnIndex]['data'] ?? null;
                if ($columnName && $columnName !== 'null' && in_array($columnName, ['tanggal_surat', 'nomor_surat', 'perihal', 'tujuan', 'created_at'])) {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('nomor_surat', 'desc');
        }

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
        $years = SuratKeluar::selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal_surat)::integer as year')
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

    public function nextNumber(Request $request)
    {
        $request->validate(['tanggal_surat' => 'sometimes|date']);
        $date = $request->input('tanggal_surat', date('Y-m-d'));
        $year = \Carbon\Carbon::parse($date)->year;
        $nextNomor = app(\App\Services\AnnualNumber::class)->next(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', $date);

        return response()->json([
            'year' => intval($year),
            'next_nomor' => $nextNomor
        ]);
    }

    public function list(Request $request)
    {
        $query = SuratKeluar::query()->with([
            'userInput:id,nama,npp',
            'userRequest:id,nama,npp',
            'bagianSeksiRequest:id,bagian_seksi,kode'
        ]);

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('CAST(nomor_surat AS TEXT) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(perihal) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(catatan) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderBy('tanggal_surat', 'desc')->orderBy('nomor_surat', 'desc');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $suratKeluar = SuratKeluar::with([
            'userInput:id,nama,npp',
            'userRequest:id,nama,npp',
            'bagianSeksiRequest:id,bagian_seksi,kode'
        ])->findOrFail($request->id);
        
        return response()->json($suratKeluar);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'nullable|integer|min:1',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'user_request' => 'nullable|uuid|exists:users,id',
            'bagian_seksi_request' => 'nullable|uuid|exists:bagian_seksi,id',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:10000',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'keluar-' . date('YmdHis') . '-' . Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        }

        $suratKeluar = app(\App\Services\AnnualNumber::class)->create(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', $validated);
        $suratKeluar->load([
            'userInput:id,nama,npp',
            'userRequest:id,nama,npp',
            'bagianSeksiRequest:id,bagian_seksi,kode'
        ]);

        return response()->json(['message' => 'Created successfully', 'data' => $suratKeluar], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $suratKeluar = SuratKeluar::findOrFail($request->id);

        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|integer|min:1',
            'tujuan' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'user_request' => 'nullable|uuid|exists:users,id',
            'bagian_seksi_request' => 'nullable|uuid|exists:bagian_seksi,id',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:10000',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'keluar-' . date('YmdHis') . '-' . Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        } else {
            unset($validated['evidence']);
        }

        $suratKeluar->update($validated);
        $suratKeluar->load([
            'userInput:id,nama,npp',
            'userRequest:id,nama,npp',
            'bagianSeksiRequest:id,bagian_seksi,kode'
        ]);

        return response()->json(['message' => 'Updated successfully', 'data' => $suratKeluar]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $suratKeluar = SuratKeluar::findOrFail($request->id);
        
        $suratKeluar->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
