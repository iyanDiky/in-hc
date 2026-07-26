<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\BagianSeksi;
use App\Models\UnitKerja;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada Jabatan
        $jabatan = Jabatan::first();
        if (!$jabatan) {
            $jabatan = Jabatan::create(['kode' => 'DUMMY_J', 'jabatan' => 'Dummy Jabatan']);
        }

        // Pastikan ada Unit Kerja untuk Bagian Seksi
        $unitKerja = UnitKerja::first();
        if (!$unitKerja) {
            $unitKerja = UnitKerja::create(['kode' => 'DUMMY_U', 'unit_kerja' => 'Dummy Unit Kerja']);
        }

        // Pastikan ada Bagian Seksi
        $bagianSeksi = BagianSeksi::first();
        if (!$bagianSeksi) {
            $bagianSeksi = BagianSeksi::create([
                'kode' => 'DUMMY_B',
                'bagian_seksi' => 'Dummy Bagian Seksi',
                'unit_kerja_id' => $unitKerja->id
            ]);
        }

        User::create([
            'npp' => 'admin-001',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jabatan_id' => $jabatan->id,
            'bagian_seksi_id' => $bagianSeksi->id,
            'username' => 'admin',
            'password' => Hash::make('divisihc**'),
            'level' => 'admin',
        ]);
    }
}
