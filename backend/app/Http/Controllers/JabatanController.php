<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Jabatan;

class JabatanController extends Controller
{
    public function datatables(Request $request)
    {
        $query = Jabatan::query();

        $recordsTotal = $query->count();

        if ($request->has('search') && !empty($request->input('search.value'))) {
            $search = strtolower($request->input('search.value'));
            $query->whereRaw('LOWER(jabatan) LIKE ?', ["%{$search}%"]);
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
            $query->orderBy('jabatan', 'asc');
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
        $query = Jabatan::query();

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(jabatan) LIKE ?', ["%{$search}%"]);
        }

        $query->orderByRaw('LOWER(jabatan) ASC');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $jabatan = Jabatan::findOrFail($request->id);
        
        return response()->json($jabatan);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('jabatan', 'kode')->whereNull('delete_at')],
            'jabatan' => 'required|string|max:255',
        ]);

        $jabatan = Jabatan::create($validated);

        return response()->json(['message' => 'Created successfully', 'data' => $jabatan], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $jabatan = Jabatan::findOrFail($request->id);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', Rule::unique('jabatan', 'kode')->ignore($jabatan->id)->whereNull('delete_at')],
            'jabatan' => 'required|string|max:255',
        ]);

        $jabatan->update($validated);

        return response()->json(['message' => 'Updated successfully', 'data' => $jabatan]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $jabatan = Jabatan::findOrFail($request->id);
        
        $jabatan->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
