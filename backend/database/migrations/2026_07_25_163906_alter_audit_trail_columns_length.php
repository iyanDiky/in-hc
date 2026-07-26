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
        $tables = ['jabatan', 'unit_kerja', 'bagian_seksi'];
        foreach ($tables as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->string('created_by', 255)->nullable()->change();
                $table->string('updated_by', 255)->nullable()->change();
                $table->string('delete_by', 255)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['jabatan', 'unit_kerja', 'bagian_seksi'];
        foreach ($tables as $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->string('created_by', 64)->nullable()->change();
                $table->string('updated_by', 64)->nullable()->change();
                $table->string('delete_by', 64)->nullable()->change();
            });
        }
    }
};
