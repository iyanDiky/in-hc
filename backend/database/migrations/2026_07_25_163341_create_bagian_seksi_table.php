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
        Schema::create('bagian_seksi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10);
            $table->string('bagian_seksi', 255);
            $table->uuid('unit_kerja_id');
            
            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja');

            // Audit Trail
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 64)->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('updated_by', 64)->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->string('delete_by', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bagian_seksi');
    }
};
