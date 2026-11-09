<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['surat_masuks', 'surat_keluars'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->index(['delete_at', 'tanggal_surat']);
                $table->index(['delete_at', 'created_at']);
            });
        }
        Schema::table('surat_masuk_disposisi', function (Blueprint $table) {
            $table->index(['surat_masuk_id', 'delete_at'], 'disposisi_surat_active_index');
            $table->index(['delete_at', 'disposisi_waktu'], 'disposisi_active_date_index');
        });
        Schema::table('surat_masuk_disposisi_tujuan', function (Blueprint $table) {
            $table->index('surat_masuk_disposisi_id', 'disposisi_tujuan_parent_index');
        });
    }

    public function down(): void
    {
        foreach (['surat_masuks', 'surat_keluars'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropIndex(['delete_at', 'tanggal_surat']);
                $table->dropIndex(['delete_at', 'created_at']);
            });
        }
        Schema::table('surat_masuk_disposisi', function (Blueprint $table) {
            $table->dropIndex('disposisi_surat_active_index');
            $table->dropIndex('disposisi_active_date_index');
        });
        Schema::table('surat_masuk_disposisi_tujuan', function (Blueprint $table) {
            $table->dropIndex('disposisi_tujuan_parent_index');
        });
    }
};
