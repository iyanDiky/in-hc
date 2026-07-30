<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_masuk_disposisi', function (Blueprint $table) {
            $table->uuid('disposisi_oleh_jabatan')->nullable()->after('disposisi_oleh');
            $table->uuid('disposisi_oleh_bagian_seksi')->nullable()->after('disposisi_oleh_jabatan');
            
            // Note: In production we'd add foreign key constraints if desired
            // $table->foreign('disposisi_oleh_jabatan')->references('id')->on('jabatan');
            // $table->foreign('disposisi_oleh_bagian_seksi')->references('id')->on('bagian_seksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_masuk_disposisi', function (Blueprint $table) {
            $table->dropColumn(['disposisi_oleh_jabatan', 'disposisi_oleh_bagian_seksi']);
        });
    }
};
