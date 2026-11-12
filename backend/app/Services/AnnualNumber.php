<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnnualNumber
{
    public function next(string $model, string $numberColumn, string $dateColumn, string $date): int
    {
        $year = Carbon::parse($date)->year;
        if ($model === \App\Models\SuratMasuk::class) {
            // The imported register restarted within 2026. Continue its LAST Excel entry,
            // rather than the largest number from the earlier numbering cycle.
            $imported = $model::query()->whereYear($dateColumn, $year)
                ->whereNotNull('import_source')->whereRaw("nomor_surat ~ '^[0-9]+$'")
                ->orderByRaw("CAST(import_source->>'row' AS INTEGER) DESC")
                ->value($numberColumn);
            // Deleted letters do not reserve numbers: continue the current active register.
            $issued = $model::query()->whereYear($dateColumn, $year)
                ->whereNull('import_source')->whereRaw("nomor_surat ~ '^[0-9]+$'")
                ->max(DB::raw('CAST(nomor_surat AS BIGINT)'));
            return max((int) $imported, (int) $issued) + 1;
        }
        // Both letter registers follow active records. Applications retain their existing policy.
        $query = $model === \App\Models\SuratKeluar::class ? $model::query() : $model::withTrashed();
        $query->where(function ($query) use ($model, $dateColumn, $year) {
            $query->whereYear($dateColumn, $year);
            if ($model === \App\Models\SuratKeluar::class) {
                $query->orWhere(fn ($q) => $q->whereNull($dateColumn)->where('tahun_arsip', $year));
            }
        });
        $max = $query->max($numberColumn);
        return (int) $max + 1;
    }

    public function create(string $model, string $numberColumn, string $dateColumn, array $attributes)
    {
        return DB::transaction(function () use ($model, $numberColumn, $dateColumn, $attributes) {
            $year = Carbon::parse($attributes[$dateColumn])->year;
            // PostgreSQL transaction lock serializes simultaneous creates in the same register/year.
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::select('SELECT pg_advisory_xact_lock(hashtext(?), ?)', [(new $model)->getTable(), $year]);
            }
            $manualOutgoing = $model === \App\Models\SuratKeluar::class && !empty($attributes[$numberColumn]);
            if (!$manualOutgoing) {
                $attributes[$numberColumn] = $this->next($model, $numberColumn, $dateColumn, $attributes[$dateColumn]);
            }
            if ($model === \App\Models\SuratKeluar::class) {
                $used = $model::query()->where($numberColumn, $attributes[$numberColumn])
                    ->where(function ($query) use ($dateColumn, $year) {
                        $query->whereYear($dateColumn, $year)
                            ->orWhere(fn ($q) => $q->whereNull($dateColumn)->where('tahun_arsip', $year));
                    })->exists();
                if ($used) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        $numberColumn => "Nomor surat {$attributes[$numberColumn]} sudah digunakan pada tahun {$year}. Pilih nomor lain.",
                    ]);
                }
            }
            $record = $model::create($attributes);
            return $record;
        });
    }
}
