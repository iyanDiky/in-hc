<?php

use App\Models\InstitusiPendidikan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institusi_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_normalized')->unique();
            $table->timestamps();
        });

        Schema::table('lamarans', function (Blueprint $table) {
            $table->foreignId('institusi_pendidikan_id')->nullable()
                ->constrained('institusi_pendidikan')->nullOnDelete();
            $table->index('institusi_pendidikan_id');
        });

        // Include historical/soft-deleted records, without changing their displayed names.
        DB::table('lamarans')->whereNotNull('institusi')->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $institution = InstitusiPendidikan::resolveName($row->institusi);
                    if ($institution) {
                        DB::table('lamarans')->where('id', $row->id)
                            ->update(['institusi_pendidikan_id' => $institution->id]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('lamarans', function (Blueprint $table) {
            $table->dropIndex(['institusi_pendidikan_id']);
            $table->dropConstrainedForeignId('institusi_pendidikan_id');
        });
        Schema::dropIfExists('institusi_pendidikan');
    }
};
