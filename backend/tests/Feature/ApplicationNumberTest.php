<?php

namespace Tests\Feature;

use App\Models\Lamaran;
use App\Services\AnnualNumber;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicationNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'application_number_test', 'database.connections.application_number_test'=>[
            'driver'=>'sqlite', 'database'=>':memory:', 'prefix'=>'', 'foreign_key_constraints'=>true,
        ]]);
        DB::purge('application_number_test');
        Schema::create('lamarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('nomor_lamaran');
            $table->date('tanggal_diterima');
            $table->string('institusi')->nullable();
            $table->integer('institusi_pendidikan_id')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('delete_by')->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('application_number_test');
        parent::tearDown();
    }

    private function next(string $date='2026-11-12'): int
    {
        return app(AnnualNumber::class)->next(Lamaran::class, 'nomor_lamaran', 'tanggal_diterima', $date);
    }

    public function test_deleted_tail_is_reused_and_history_is_retained(): void
    {
        Lamaran::create(['nomor_lamaran'=>52,'tanggal_diterima'=>'2026-11-09']);
        $tail = Lamaran::create(['nomor_lamaran'=>53,'tanggal_diterima'=>'2026-11-12']);
        $tail->delete();
        $this->assertSame(53, $this->next());
        $new = app(AnnualNumber::class)->create(Lamaran::class, 'nomor_lamaran', 'tanggal_diterima', ['tanggal_diterima'=>'2026-11-12']);
        $this->assertSame(53, (int)$new->nomor_lamaran);
        $this->assertSame(54, $this->next());
        $this->assertTrue(Lamaran::withTrashed()->findOrFail($tail->id)->trashed());
    }

    public function test_deleting_middle_does_not_fill_the_gap(): void
    {
        $middle = Lamaran::create(['nomor_lamaran'=>52,'tanggal_diterima'=>'2026-11-09']);
        Lamaran::create(['nomor_lamaran'=>53,'tanggal_diterima'=>'2026-11-12']);
        $middle->delete();
        $this->assertSame(54, $this->next());
    }

    public function test_years_are_separate_and_new_year_starts_at_one(): void
    {
        Lamaran::create(['nomor_lamaran'=>52,'tanggal_diterima'=>'2026-11-09']);
        $deleted = Lamaran::create(['nomor_lamaran'=>1,'tanggal_diterima'=>'2027-01-01']);
        $deleted->delete();
        $this->assertSame(53, $this->next());
        $this->assertSame(1, $this->next('2027-01-01'));
        $new = app(AnnualNumber::class)->create(Lamaran::class, 'nomor_lamaran', 'tanggal_diterima', ['tanggal_diterima'=>'2027-01-01']);
        $this->assertSame(1, (int)$new->nomor_lamaran);
    }

    public function test_no_active_applications_starts_at_one(): void
    {
        $deleted = Lamaran::create(['nomor_lamaran'=>99,'tanggal_diterima'=>'2026-11-09']);
        $deleted->delete();
        $this->assertSame(1, $this->next());
    }
}
