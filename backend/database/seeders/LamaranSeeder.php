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
        $admin = User::first();
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

        foreach ($data as $item) {
            Lamaran::create([
                'nomor_lamaran' => $item['nomor_lamaran'],
                'tanggal_diterima' => $item['tanggal_diterima'],
                'nama' => $item['nama'],
                'tempat_lahir' => $item['tempat_lahir'] ?? null,
                'tanggal_lahir' => $item['tanggal_lahir'] ?? null,
                'pendidikan' => $item['pendidikan'] ?? 'S1',
                'institusi' => $item['institusi'] ?? 'Lainnya',
                'jurusan' => $item['jurusan'] ?? null,
                'catatan' => $item['catatan'] ?? null,
                'user_input' => $adminId,
            ]);
        }

        $this->command->info('Seeding Lamaran berhasil: ' . count($data) . ' data dimasukkan.');
    }
}
