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
        Schema::create('surat_masuk_disposisi_tujuan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('surat_masuk_disposisi_id');
            $table->uuid('tujuan_disposisi');
            
            // Audit trail
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('updated_by', 255)->nullable();
            $table->dateTime('delete_at')->nullable();
            $table->string('delete_by', 255)->nullable();

            $table->foreign('surat_masuk_disposisi_id')->references('id')->on('surat_masuk_disposisi')->onDelete('cascade');
            $table->foreign('tujuan_disposisi')->references('id')->on('bagian_seksi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_disposisi_tujuan');
    }
};
