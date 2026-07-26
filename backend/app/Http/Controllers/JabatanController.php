<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Jabatan;

class JabatanController extends Controller
{
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
