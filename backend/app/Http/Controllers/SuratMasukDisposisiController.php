<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasukDisposisi;
use App\Models\SuratMasukDisposisiTujuan;
use App\Models\SuratMasuk;
use Illuminate\Support\Facades\DB;

class SuratMasukDisposisiController extends Controller
{
    public function index($suratMasukId)
    {
        $suratMasuk = SuratMasuk::findOrFail($suratMasukId);
        
        $disposisi = SuratMasukDisposisi::with(['disposisiOleh', 'jabatan', 'bagianSeksi', 'tujuans.tujuan'])
            ->where('surat_masuk_id', $suratMasukId)
            ->orderBy('disposisi_waktu', 'asc')
            ->get();
            
        return response()->json([
            'message' => 'Success',
            'data' => $disposisi
        ]);
    }

    public function store(Request $request, $suratMasukId)
    {
        $request->validate([
            'catatan' => 'required|string',
            'tujuan_bagian_seksi_ids' => 'required|array|min:1',
            'tujuan_bagian_seksi_ids.*' => 'uuid|exists:bagian_seksi,id',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'disposisi_oleh' => 'nullable|uuid|exists:users,id',
            'disposisi_oleh_jabatan' => 'nullable|uuid|exists:jabatan,id',
            'disposisi_oleh_bagian_seksi' => 'nullable|uuid|exists:bagian_seksi,id'
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = date('YmdHis') . '-disposisi-' . \Illuminate\Support\Facades\Auth::id() . '.' . $file->extension();
            $evidencePath = $file->storeAs('evidence', $filename, 'public');
        }

        $suratMasuk = SuratMasuk::findOrFail($suratMasukId);

        DB::beginTransaction();
        try {
            $disposisi = SuratMasukDisposisi::create([
                'surat_masuk_id' => $suratMasukId,
                'disposisi_oleh' => $request->disposisi_oleh ?? auth()->id(),
                'disposisi_oleh_jabatan' => $request->disposisi_oleh_jabatan,
                'disposisi_oleh_bagian_seksi' => $request->disposisi_oleh_bagian_seksi,
                'catatan' => $request->catatan,
                'evidence' => $evidencePath
            ]);

            foreach ($request->tujuan_bagian_seksi_ids as $bagianSeksiId) {
                SuratMasukDisposisiTujuan::create([
                    'surat_masuk_disposisi_id' => $disposisi->id,
                    'tujuan_disposisi' => $bagianSeksiId
                ]);
            }

            DB::commit();

            // Load relations to return the complete object
            $disposisi->load(['disposisiOleh', 'jabatan', 'bagianSeksi', 'tujuans.tujuan']);

            return response()->json([
                'message' => 'Disposisi created successfully',
                'data' => $disposisi
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create disposisi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($suratMasukId, $disposisiId)
    {
        $disposisi = SuratMasukDisposisi::where('surat_masuk_id', $suratMasukId)
            ->where('id', $disposisiId)
            ->firstOrFail();

        $disposisi->delete();

        return response()->json([
            'message' => 'Disposisi deleted successfully'
        ]);
    }
}
