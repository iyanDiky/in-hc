<?php

namespace Database\Seeders;

use App\Models\BagianSeksi;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SuratKeluarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('nama', 'The Admin')->first() ?? User::where('username', 'admin')->first() ?? User::first();
        if ($admin) {
            Auth::login($admin);
        }

        $jsonPath = database_path('data/surat_keluar.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("File {$jsonPath} tidak ditemukan.");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);
        if (!is_array($data)) {
            $this->command->error("Format JSON di {$jsonPath} tidak valid.");
            return;
        }

        $adminId = $admin ? $admin->id : null;
        $adminBagianSeksiId = $admin ? $admin->bagian_seksi_id : null;
        $baseCreatedAt = \Carbon\Carbon::parse('2025-09-01 08:00:00');

        $bagianSeksiMap = BagianSeksi::all()->keyBy('kode');

        foreach ($data as $index => $item) {
            $createdAt = (clone $baseCreatedAt)->addMinutes($index * 5);

            $bagianSeksiId = null;
            if (!empty($item['bidang_kode']) && isset($bagianSeksiMap[$item['bidang_kode']])) {
                $bagianSeksiId = $bagianSeksiMap[$item['bidang_kode']]->id;
            } else {
                $bagianSeksiId = $adminBagianSeksiId;
            }

            SuratKeluar::create([
                'nomor_surat' => $item['nomor_surat'],
                'tanggal_surat' => $item['tanggal_surat'],
                'tujuan' => $item['tujuan'] ?? '-',
                'perihal' => $item['perihal'] ?? '-',
                'evidence' => null,
                'catatan' => null,
                'user_input' => $adminId,
                'user_request' => $adminId,
                'bagian_seksi_request' => $bagianSeksiId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Seeding Surat Keluar berhasil: ' . count($data) . ' data dimasukkan.');
    }
}
