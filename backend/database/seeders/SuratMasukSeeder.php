<?php

namespace Database\Seeders;

use App\Models\BagianSeksi;
use App\Models\SuratMasuk;
use App\Models\SuratMasukDisposisi;
use App\Models\SuratMasukDisposisiTujuan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SuratMasukSeeder extends Seeder
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

        $jsonPath = database_path('data/surat_masuk.json');
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
        $adminJabatanId = $admin ? $admin->jabatan_id : null;
        $adminBagianSeksiId = $admin ? $admin->bagian_seksi_id : null;
        $baseCreatedAt = \Carbon\Carbon::parse('2025-08-25 08:00:00');

        // Cache BagianSeksi by kode (e.g. 'PPH', 'KM', 'PPL', etc.)
        $bagianSeksiMap = BagianSeksi::all()->keyBy('kode');

        $disposisiCount = 0;
        $tujuanCount = 0;

        foreach ($data as $index => $item) {
            $createdAt = (clone $baseCreatedAt)->addMinutes($index * 5);

            $suratMasuk = SuratMasuk::create([
                'nomor_surat' => $item['nomor_surat'] ?? '-',
                'tanggal_surat' => $item['tanggal_surat'],
                'pengirim' => $item['pengirim'] ?? '-',
                'tujuan' => $item['tujuan'] ?? 'Bank Kalsel',
                'perihal' => $item['perihal'] ?? '-',
                'evidence' => null,
                'catatan' => null,
                'user_input' => $adminId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Buat disposisi jika terdapat target disposisi
            $disposisiKodes = $item['disposisi_target_kodes'] ?? [];
            if (!empty($disposisiKodes)) {
                $targetBagianIds = [];
                foreach ($disposisiKodes as $kode) {
                    if (isset($bagianSeksiMap[$kode])) {
                        $targetBagianIds[] = $bagianSeksiMap[$kode]->id;
                    }
                }

                if (!empty($targetBagianIds)) {
                    $disposisi = SuratMasukDisposisi::create([
                        'surat_masuk_id' => $suratMasuk->id,
                        'disposisi_oleh' => $adminId,
                        'disposisi_oleh_jabatan' => $adminJabatanId,
                        'disposisi_oleh_bagian_seksi' => $adminBagianSeksiId,
                        'disposisi_waktu' => $createdAt,
                        'catatan' => 'Tindak Lanjut',
                        'evidence' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    $disposisiCount++;

                    foreach ($targetBagianIds as $bagianId) {
                        SuratMasukDisposisiTujuan::create([
                            'surat_masuk_disposisi_id' => $disposisi->id,
                            'tujuan_disposisi' => $bagianId,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                        $tujuanCount++;
                    }
                }
            }
        }

        $this->command->info('Seeding Surat Masuk berhasil: ' . count($data) . ' surat masuk dimasukkan.');
        $this->command->info("Dibuat {$disposisiCount} data disposisi dengan {$tujuanCount} tujuan disposisi.");
    }
}
