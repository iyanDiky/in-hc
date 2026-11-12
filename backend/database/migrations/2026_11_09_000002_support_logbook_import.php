<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['surat_masuks', 'surat_keluars', 'lamarans'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->jsonb('import_issues')->nullable();
                $table->jsonb('import_source')->nullable();
            });
        }
        // Keep an unknown date unknown; the archive year is explicitly marked as inferred.
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE surat_keluars ALTER COLUMN tanggal_surat DROP NOT NULL');
        Schema::table('surat_keluars', fn (Blueprint $table) => $table->smallInteger('tahun_arsip')->nullable());
        foreach (['surat_masuks', 'surat_keluars', 'lamarans'] as $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ALTER COLUMN catatan TYPE text");
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('surat_keluars')->whereNull('tanggal_surat')->exists()) {
            throw new RuntimeException('Lengkapi tanggal surat keluar sebelum rollback.');
        }
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE surat_keluars ALTER COLUMN tanggal_surat SET NOT NULL');
        Schema::table('surat_keluars', fn (Blueprint $table) => $table->dropColumn('tahun_arsip'));
        foreach (['surat_masuks', 'surat_keluars', 'lamarans'] as $table) {
            Schema::table($table, fn (Blueprint $table) => $table->dropColumn(['import_issues', 'import_source']));
        }
    }
};
