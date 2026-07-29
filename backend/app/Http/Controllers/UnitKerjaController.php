<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\UnitKerja;

class UnitKerjaController extends Controller
{
    public function datatables(Request $request)
    {
        $query = UnitKerja::query();

        $recordsTotal = $query->count();

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $search = strtolower($request->input('search.value'));
            $query->whereRaw('LOWER(unit_kerja) LIKE ?', ["%{$search}%"]);
        }

        $recordsFiltered = $query->count();

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
            $query->orderBy('unit_kerja', 'asc');
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $query->get()
        ]);
    }

    public function list(Request $request)
    {
        $query = UnitKerja::query();

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(unit_kerja) LIKE ?', ["%{$search}%"]);
        }

        $query->orderByRaw('LOWER(unit_kerja) ASC');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $unit_kerja = UnitKerja::findOrFail($request->id);
        
        return response()->json($unit_kerja);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('unit_kerja', 'kode')->whereNull('delete_at')],
            'unit_kerja' => 'required|string|max:255',
        ]);

        $unit_kerja = UnitKerja::create($validated);

        return response()->json(['message' => 'Created successfully', 'data' => $unit_kerja], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $unit_kerja = UnitKerja::findOrFail($request->id);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('unit_kerja', 'kode')->ignore($unit_kerja->id)->whereNull('delete_at')],
            'unit_kerja' => 'required|string|max:255',
        ]);

        $unit_kerja->update($validated);

        return response()->json(['message' => 'Updated successfully', 'data' => $unit_kerja]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $unit_kerja = UnitKerja::findOrFail($request->id);
        
        $unit_kerja->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
