<?php

namespace Database\Seeders;

use App\Models\BagianSeksi;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Unit Kerja ada
        $unitKerja = UnitKerja::firstOrCreate(
            ['kode' => 'DHC'],
            ['unit_kerja' => 'Divisi Human Capital']
        );

        // 2. Pastikan Bagian Seksi lengkap
        $bagianSeksiList = [
            ['kode' => 'KADIV', 'bagian_seksi' => 'Kepala Divisi'],
            ['kode' => 'PPH', 'bagian_seksi' => 'Bagian Penerimaan & Pengembangan'],
            ['kode' => 'KM', 'bagian_seksi' => 'Bagian Kompensasi & Manfaat'],
            ['kode' => 'PPL', 'bagian_seksi' => 'Bagian Pelatihan & Pendidikan'],
            ['kode' => 'DE', 'bagian_seksi' => 'Data Entry'],
        ];

        foreach ($bagianSeksiList as $b) {
            BagianSeksi::firstOrCreate(
                ['kode' => $b['kode']],
                [
                    'bagian_seksi' => $b['bagian_seksi'],
                    'unit_kerja_id' => $unitKerja->id,
                ]
            );
        }

        // 3. Pastikan Jabatan lengkap
        $jabatanList = [
            ['kode' => 'PGS.KADIV', 'jabatan' => 'Pgs. Kepala Divisi'],
            ['kode' => 'KABAG', 'jabatan' => 'Kepala Bagian'],
            ['kode' => 'PGS.KABAG', 'jabatan' => 'Pgs. Kepala Bagian'],
            ['kode' => 'SR.ANS', 'jabatan' => 'Sr. Analis'],
            ['kode' => 'ANS', 'jabatan' => 'Analis'],
            ['kode' => 'JR.ANS', 'jabatan' => 'Jr. Analis'],
            ['kode' => 'STF', 'jabatan' => 'Staf'],
            ['kode' => 'PLT.KADIV', 'jabatan' => 'Plt. Kepala Divisi'],
            ['kode' => 'KADIV', 'jabatan' => 'Kepala Divisi'],
        ];

        foreach ($jabatanList as $j) {
            Jabatan::firstOrCreate(
                ['kode' => $j['kode']],
                ['jabatan' => $j['jabatan']]
            );
        }

        $defaultJabatan = Jabatan::where('kode', 'STF')->first() ?? Jabatan::first();
        $defaultBagian = BagianSeksi::where('kode', 'DE')->first() ?? BagianSeksi::first();

        // 4. Pastikan akun Admin ada
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'npp' => '005',
                'nama' => 'The Admin',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'jabatan_id' => $defaultJabatan->id,
                'bagian_seksi_id' => $defaultBagian->id,
                'password' => Hash::make('Bankkalsel1*'),
                'level' => 'admin',
            ]
        );

        Auth::login($admin);

        // 5. Load data user dari user.json
        $jsonPath = database_path('data/user.json');
        if (!File::exists($jsonPath)) {
            $this->command->warn("File {$jsonPath} tidak ditemukan, melewati seeding user tambahan.");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);
        if (!is_array($data)) {
            $this->command->error("Format JSON di {$jsonPath} tidak valid.");
            return;
        }

        $jabatanMap = Jabatan::all()->keyBy('kode');
        $bagianMap = BagianSeksi::all()->keyBy('kode');

        $seededCount = 0;
        foreach ($data as $item) {
            $jabatanId = isset($jabatanMap[$item['jabatan_kode']]) 
                ? $jabatanMap[$item['jabatan_kode']]->id 
                : $defaultJabatan->id;

            $bagianSeksiId = isset($bagianMap[$item['bagian_seksi_kode']]) 
                ? $bagianMap[$item['bagian_seksi_kode']]->id 
                : $defaultBagian->id;

            User::updateOrCreate(
                ['username' => $item['username']],
                [
                    'npp' => $item['npp'],
                    'nama' => $item['nama'],
                    'tempat_lahir' => $item['tempat_lahir'] ?? '-',
                    'tanggal_lahir' => $item['tanggal_lahir'] ?? '1990-01-01',
                    'jabatan_id' => $jabatanId,
                    'bagian_seksi_id' => $bagianSeksiId,
                    'password' => Hash::make('Bankkalsel1*'),
                    'level' => $item['level'] ?? 'user',
                ]
            );
            $seededCount++;
        }

        $this->command->info("Seeding User berhasil: {$seededCount} user dari data_raw/user.xlsx diproses.");
    }
}
