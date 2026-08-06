<?php

namespace Database\Seeders;

use App\Models\Lamaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LamaranSeeder extends Seeder
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

        $jsonPath = database_path('data/lamaran.json');
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
        $baseCreatedAt = \Carbon\Carbon::parse('2025-10-01 08:00:00');

        foreach ($data as $index => $item) {
            $createdAt = (clone $baseCreatedAt)->addMinutes($index * 10);

            Lamaran::updateOrCreate(
                [
                    'nomor_lamaran' => $item['nomor_lamaran'],
                    'nama' => $item['nama'],
                    'tanggal_diterima' => $item['tanggal_diterima'],
                ],
                [
                    'tempat_lahir' => $item['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $item['tanggal_lahir'] ?? null,
                    'pendidikan' => $item['pendidikan'] ?? 'LAINNYA',
                    'institusi' => $item['institusi'] ?? 'Lainnya',
                    'jurusan' => $item['jurusan'] ?? '-',
                    'evidence' => null,
                    'catatan' => null,
                    'user_input' => $adminId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }

        $this->command->info('Seeding Lamaran berhasil: ' . count($data) . ' data dimasukkan.');
    }
}
