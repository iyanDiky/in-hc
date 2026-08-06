<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lamaran;
use Illuminate\Support\Facades\Auth;

class LamaranController extends Controller
{
    public function datatables(Request $request)
    {
        $query = Lamaran::query()->with([
            'userInput:id,nama,npp'
        ]);

        // Total count before any filter
        $recordsTotal = Lamaran::count();

        // 1. Date Range Filter (tanggal_diterima)
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_diterima', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_diterima', '<=', $request->input('end_date'));
        }

        // 2. Pendidikan Filter
        if ($request->filled('pendidikan')) {
            $query->where('pendidikan', $request->input('pendidikan'));
        }

        // 3. Global Search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $search = strtolower($searchValue);
            $query->where(function($q) use ($search) {
                $q->whereRaw('CAST(nomor_lamaran AS TEXT) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(nama) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(tempat_lahir) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(institusi) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(jurusan) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(catatan) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('userInput', function($uq) use ($search) {
                      $uq->whereRaw('LOWER(nama) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(npp) LIKE ?', ["%{$search}%"]);
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
                if ($columnName && $columnName !== 'null' && in_array($columnName, ['tanggal_diterima', 'nomor_lamaran', 'nama', 'pendidikan', 'institusi', 'jurusan', 'created_at'])) {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('tanggal_diterima', 'desc');
        }

        // 5. Pagination
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

    public function nextNumber(Request $request)
    {
        $tanggalDiterima = $request->input('tanggal_diterima', date('Y-m-d'));
        $year = date('Y', strtotime($tanggalDiterima));
        $maxNomor = Lamaran::whereYear('tanggal_diterima', $year)->max('nomor_lamaran');
        $nextNomor = ($maxNomor !== null) ? intval($maxNomor) + 1 : 1;

        return response()->json([
            'year' => intval($year),
            'next_nomor' => $nextNomor
        ]);
    }

    public function list(Request $request)
    {
        $query = Lamaran::query()->with([
            'userInput:id,nama,npp'
        ]);

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('CAST(nomor_lamaran AS TEXT) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(nama) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(institusi) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(jurusan) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderBy('tanggal_diterima', 'desc')->orderBy('nomor_lamaran', 'desc');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $lamaran = Lamaran::with([
            'userInput:id,nama,npp'
        ])->findOrFail($request->id);
        
        return response()->json($lamaran);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'tanggal_diterima' => 'required|date',
            'nomor_lamaran' => 'nullable|integer|min:1',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|string|max:255',
            'pendidikan' => 'required|in:SMA,D3,D4,S1,S2,S3,LAINNYA',
            'institusi' => 'nullable|string|max:255',
            'jurusan' => 'nullable|string|max:255',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:255',
        ]);

        // Auto increment nomor_lamaran per tahun
        if (empty($validated['nomor_lamaran'])) {
            $year = date('Y', strtotime($validated['tanggal_diterima']));
            $maxNomor = Lamaran::whereYear('tanggal_diterima', $year)->max('nomor_lamaran');
            $validated['nomor_lamaran'] = ($maxNomor !== null) ? intval($maxNomor) + 1 : 1;
        }

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'pelamar-' . date('YmdHis') . '-' . Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        }

        $lamaran = Lamaran::create($validated);
        $lamaran->load([
            'userInput:id,nama,npp'
        ]);

        return response()->json(['message' => 'Created successfully', 'data' => $lamaran], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $lamaran = Lamaran::findOrFail($request->id);

        $validated = $request->validate([
            'tanggal_diterima' => 'required|date',
            'nomor_lamaran' => 'required|integer|min:1',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|string|max:255',
            'pendidikan' => 'required|in:SMA,D3,D4,S1,S2,S3,LAINNYA',
            'institusi' => 'nullable|string|max:255',
            'jurusan' => 'nullable|string|max:255',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'pelamar-' . date('YmdHis') . '-' . Auth::id() . '.' . $file->extension();
            $path = $file->storeAs('evidence', $filename, 'public');
            $validated['evidence'] = $path;
        } else {
            unset($validated['evidence']);
        }

        $lamaran->update($validated);
        $lamaran->load([
            'userInput:id,nama,npp'
        ]);

        return response()->json(['message' => 'Updated successfully', 'data' => $lamaran]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $lamaran = Lamaran::findOrFail($request->id);
        
        $lamaran->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
