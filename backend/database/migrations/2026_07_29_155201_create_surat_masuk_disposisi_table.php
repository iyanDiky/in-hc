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
        Schema::create('surat_masuk_disposisi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('surat_masuk_id');
            $table->uuid('disposisi_oleh');
            $table->dateTime('disposisi_waktu');
            $table->text('catatan')->nullable();
            $table->string('evidence', 255)->nullable();
            
            // Audit trail
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('updated_by', 255)->nullable();
            $table->dateTime('delete_at')->nullable();
            $table->string('delete_by', 255)->nullable();

            $table->foreign('surat_masuk_id')->references('id')->on('surat_masuks')->onDelete('cascade');
            $table->foreign('disposisi_oleh')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_disposisi');
    }
};
