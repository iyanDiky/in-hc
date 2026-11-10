<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitusiPendidikan extends Model
{
    protected $table = 'institusi_pendidikan';

    public static function cleanName(?string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', $name ?? ''));
    }

    public static function nameKey(?string $name): string
    {
        return mb_strtolower(self::cleanName($name), 'UTF-8');
    }

    public static function resolveName(?string $name): ?self
    {
        $name = self::cleanName($name);
        if ($name === '') {
            return null;
        }

        $key = self::nameKey($name);
        // Database uniqueness also protects simultaneous submissions of the same name.
        self::query()->insertOrIgnore([
            'nama' => $name,
            'nama_normalized' => $key,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return self::where('nama_normalized', $key)->firstOrFail();
    }
}
