<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Services\AnnualNumber;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OutgoingNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'outgoing_number_test', 'database.connections.outgoing_number_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('outgoing_number_test');
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('nomor_surat');
            $table->date('tanggal_surat')->nullable();
            $table->integer('tahun_arsip')->nullable();
            $table->string('tujuan')->nullable();
            $table->string('perihal')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('delete_by')->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('outgoing_number_test');
        parent::tearDown();
    }

    private function next(string $date = '2026-11-12'): int
    {
        return app(AnnualNumber::class)->next(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', $date);
    }

    public function test_manual_backdated_number_is_preserved_and_does_not_lower_the_next_number(): void
    {
        SuratKeluar::create(['nomor_surat'=>262,'tanggal_surat'=>'2026-11-12']);
        $manual = app(AnnualNumber::class)->create(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', [
            'tanggal_surat'=>'2026-10-01', 'nomor_surat'=>200,
        ]);
        $this->assertSame(200, (int)$manual->nomor_surat);
        $this->assertSame('2026-10-01', $manual->tanggal_surat);
        $this->assertSame(263, $this->next());
    }

    public function test_active_duplicate_number_in_the_same_year_is_rejected(): void
    {
        SuratKeluar::create(['nomor_surat'=>20,'tanggal_surat'=>null,'tahun_arsip'=>2026]);
        try {
            app(AnnualNumber::class)->create(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', [
                'tanggal_surat'=>'2026-10-01', 'nomor_surat'=>20,
            ]);
            $this->fail('An active duplicate should fail validation.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('nomor_surat', $exception->errors());
        }
        $this->assertSame(1, SuratKeluar::count());
    }

    public function test_manual_number_can_reuse_a_deleted_number_or_a_number_from_another_year(): void
    {
        SuratKeluar::create(['nomor_surat'=>20,'tanggal_surat'=>'2025-10-01']);
        $deleted = SuratKeluar::create(['nomor_surat'=>20,'tanggal_surat'=>'2026-10-01']);
        $deleted->delete();
        $manual = app(AnnualNumber::class)->create(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', [
            'tanggal_surat'=>'2026-09-01', 'nomor_surat'=>20,
        ]);
        $this->assertSame(20, (int)$manual->nomor_surat);
    }

    public function test_deleted_tail_is_reused_on_create(): void
    {
        SuratKeluar::create(['nomor_surat'=>163,'tanggal_surat'=>'2026-11-12']);
        $tail = SuratKeluar::create(['nomor_surat'=>164,'tanggal_surat'=>'2026-11-12']);
        $tail->delete();
        $this->assertSame(164, $this->next());
        $new = app(AnnualNumber::class)->create(SuratKeluar::class, 'nomor_surat', 'tanggal_surat', ['tanggal_surat'=>'2026-11-12']);
        $this->assertSame(164, (int)$new->nomor_surat);
        $this->assertSame(165, $this->next());
        $this->assertTrue(SuratKeluar::withTrashed()->findOrFail($tail->id)->trashed());
    }

    public function test_deleting_middle_keeps_the_highest_active_number(): void
    {
        $middle = SuratKeluar::create(['nomor_surat'=>163,'tanggal_surat'=>'2026-11-12']);
        SuratKeluar::create(['nomor_surat'=>164,'tanggal_surat'=>'2026-11-12']);
        $middle->delete();
        $this->assertSame(165, $this->next());
    }

    public function test_years_are_separate_and_an_empty_year_starts_at_one(): void
    {
        SuratKeluar::create(['nomor_surat'=>260,'tanggal_surat'=>'2026-11-12']);
        $deleted = SuratKeluar::create(['nomor_surat'=>1,'tanggal_surat'=>'2027-01-01']);
        $deleted->delete();
        $this->assertSame(261, $this->next());
        $this->assertSame(1, $this->next('2027-01-01'));
    }

    public function test_undated_archives_only_count_when_active_and_in_the_same_year(): void
    {
        SuratKeluar::create(['nomor_surat'=>20,'tanggal_surat'=>null,'tahun_arsip'=>2026]);
        $deleted = SuratKeluar::create(['nomor_surat'=>99,'tanggal_surat'=>null,'tahun_arsip'=>2026]);
        $deleted->delete();
        SuratKeluar::create(['nomor_surat'=>500,'tanggal_surat'=>null,'tahun_arsip'=>2025]);
        $this->assertSame(21, $this->next());
    }
}
