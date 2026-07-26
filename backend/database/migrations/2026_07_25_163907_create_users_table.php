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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('npp', 16)->unique();
            $table->string('tempat_lahir', 255);
            $table->date('tanggal_lahir');
            $table->uuid('jabatan_id');
            $table->uuid('bagian_seksi_id');
            $table->string('username', 255)->unique();
            $table->string('password', 255);
            $table->enum('level', ['admin', 'user']);

            // Definisi Foreign Key
            $table->foreign('jabatan_id')->references('id')->on('jabatan');
            $table->foreign('bagian_seksi_id')->references('id')->on('bagian_seksi');

            // Audit Trail
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('updated_by', 255)->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->string('delete_by', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
