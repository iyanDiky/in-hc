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
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal_surat');
            $table->integer('nomor_surat')->index();
            $table->string('tujuan');
            $table->string('perihal');
            $table->string('evidence')->nullable();
            $table->string('catatan')->nullable();
            $table->uuid('user_input')->index();
            $table->uuid('user_request')->nullable()->index();
            $table->uuid('bagian_seksi_request')->nullable()->index();

            // Audit Trail
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('updated_by', 255)->nullable();
            
            $table->dateTime('delete_at')->nullable();
            $table->string('delete_by', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};
