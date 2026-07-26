<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $query = User::with(['jabatan', 'bagianSeksi']);

        if ($request->has('search')) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(npp) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(username) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->orderByRaw('LOWER(username) ASC');

        $limit = $request->input('limit', 10);
        return response()->json($query->paginate($limit));
    }

    public function detail(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $user = User::with(['jabatan', 'bagianSeksi'])->findOrFail($request->id);
        
        return response()->json($user);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'npp' => ['required', 'string', 'max:16', Rule::unique('users', 'npp')->whereNull('delete_at')],
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jabatan_id' => 'required|uuid|exists:jabatan,id',
            'bagian_seksi_id' => 'required|uuid|exists:bagian_seksi,id',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->whereNull('delete_at')],
            'password' => 'required|string|max:255',
            'level' => 'required|in:admin,user',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json(['message' => 'Created successfully', 'data' => $user], 201);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $user = User::findOrFail($request->id);

        $validated = $request->validate([
            'npp' => ['required', 'string', 'max:16', Rule::unique('users', 'npp')->ignore($user->id)->whereNull('delete_at')],
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jabatan_id' => 'required|uuid|exists:jabatan,id',
            'bagian_seksi_id' => 'required|uuid|exists:bagian_seksi,id',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)->whereNull('delete_at')],
            'password' => 'nullable|string|max:255',
            'level' => 'required|in:admin,user',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'Updated successfully', 'data' => $user]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|uuid']);
        $user = User::findOrFail($request->id);
        
        $user->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
