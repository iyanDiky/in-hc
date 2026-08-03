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
        Schema::create('lamarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('nomor_lamaran')->index();
            $table->date('tanggal_diterima')->index();
            $table->string('nama', 255);
            $table->string('tempat_lahir', 255)->nullable();
            $table->string('tanggal_lahir', 255)->nullable();
            $table->enum('pendidikan', ['SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'LAINNYA'])->default('S1');
            $table->string('institusi', 255)->nullable();
            $table->string('jurusan', 255)->nullable();
            $table->string('evidence', 255)->nullable();
            $table->string('catatan', 255)->nullable();
            $table->uuid('user_input')->index();

            // Audit Trail & Soft Delete
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('updated_by', 255)->nullable();
            $table->dateTime('delete_at')->nullable();
            $table->string('delete_by', 255)->nullable();

            $table->foreign('user_input')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamarans');
    }
};
