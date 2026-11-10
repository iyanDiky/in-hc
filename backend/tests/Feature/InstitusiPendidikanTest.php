<?php

namespace Tests\Feature;

use App\Models\InstitusiPendidikan;
use App\Models\Lamaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InstitusiPendidikanTest extends TestCase
{
    private $migration;

    protected function setUp(): void
    {
        parent::setUp();
        // A separate in-memory connection: these tests never migrate the application database.
        config(['database.default' => 'institution_test', 'database.connections.institution_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::purge('institution_test');
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('npp')->nullable();
        });
        Schema::create('lamarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('institusi')->nullable();
            $table->string('nama')->nullable();
            $table->string('pendidikan')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->integer('nomor_lamaran')->nullable();
            $table->uuid('user_input')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->timestamps();
        });
        $this->migration = require database_path('migrations/2026_11_10_000001_create_institusi_pendidikan.php');
    }

    protected function tearDown(): void
    {
        DB::disconnect('institution_test');
        parent::tearDown();
    }

    public function test_backfill_preserves_names_and_merges_case_and_spacing(): void
    {
        DB::table('lamarans')->insert([
            ['id' => 'old-a', 'institusi' => ' Universitas   Indonesia ', 'delete_at' => null],
            ['id' => 'old-b', 'institusi' => 'universitas indonesia', 'delete_at' => now()],
            ['id' => 'old-c', 'institusi' => '   ', 'delete_at' => null],
        ]);
        $this->migration->up();
        $this->assertSame(1, InstitusiPendidikan::count());
        $rows = DB::table('lamarans')->orderBy('id')->get();
        $this->assertSame($rows[0]->institusi_pendidikan_id, $rows[1]->institusi_pendidikan_id);
        $this->assertSame(' Universitas   Indonesia ', $rows[0]->institusi);
        $this->assertNull($rows[2]->institusi_pendidikan_id);
    }

    public function test_create_edit_and_clear_share_the_master(): void
    {
        $this->migration->up();
        $first = Lamaran::create(['institusi' => 'Universitas Indonesia']);
        $second = Lamaran::create(['institusi' => ' UNIVERSITAS   INDONESIA ']);
        $this->assertSame($first->institusi_pendidikan_id, $second->institusi_pendidikan_id);
        $this->assertSame('Universitas Indonesia', $second->institusi);
        $second->update(['institusi' => 'Politeknik Negeri']);
        $this->assertSame(2, InstitusiPendidikan::count());
        $this->assertNotEquals($first->institusi_pendidikan_id, $second->institusi_pendidikan_id);
        $second->update(['institusi' => null]);
        $this->assertNull($second->institusi_pendidikan_id);
        $this->assertNull($second->institusi);
    }

    public function test_failed_applicant_save_rolls_back_new_institution(): void
    {
        $this->migration->up();
        try {
            DB::transaction(function () {
                Lamaran::create(['institusi' => 'Tidak Tersimpan']);
                throw new \RuntimeException('Simulated failed submission');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated failed submission', $exception->getMessage());
        }
        $this->assertSame(0, InstitusiPendidikan::count());
        $this->assertSame(0, Lamaran::count());
    }

    public function test_search_is_authenticated_limited_and_does_not_create_names(): void
    {
        $this->migration->up();
        $this->postJson('/api/institusi-pendidikan/search', ['search' => 'Un'])->assertUnauthorized();
        Sanctum::actingAs(new User(['id' => 'search-user']));
        for ($i = 0; $i < 12; $i++) {
            InstitusiPendidikan::resolveName("Universitas {$i}");
        }
        InstitusiPendidikan::resolveName('Kampus 100%');
        $this->postJson('/api/institusi-pendidikan/search', ['search' => 'UNIVERSITAS'])->assertOk()->assertJsonCount(10, 'data');
        $this->postJson('/api/institusi-pendidikan/search', ['search' => 'u'])->assertOk()->assertExactJson(['data' => []]);
        $this->postJson('/api/institusi-pendidikan/search', ['search' => 'Tidak Ada'])->assertOk()->assertExactJson(['data' => []]);
        $this->postJson('/api/institusi-pendidikan/search', ['search' => '100%'])->assertOk()->assertJsonPath('data.0.nama', 'Kampus 100%')->assertJsonCount(1, 'data');
        $this->postJson('/api/institusi-pendidikan/search', ['search' => str_repeat('a', 256)])->assertUnprocessable();
        $this->assertSame(13, InstitusiPendidikan::count());
    }

    public function test_applicant_api_creates_reuses_and_clears_institutions_only_after_validation(): void
    {
        $this->migration->up();
        Sanctum::actingAs(new User(['id' => 'applicant-user']));
        $payload = ['tanggal_diterima' => '2026-11-10', 'nama' => 'Pelamar Uji', 'pendidikan' => 'S1', 'institusi' => 'Kampus Baru'];
        $this->postJson('/api/lamaran/create', array_merge($payload, ['nama' => '']))->assertUnprocessable();
        $this->assertSame(0, InstitusiPendidikan::count());
        $saved = $this->postJson('/api/lamaran/create', $payload)->assertCreated()->json('data');
        $this->assertNotNull($saved['institusi_pendidikan_id']);
        $this->postJson('/api/lamaran/update', array_merge($payload, [
            'id' => $saved['id'], 'nomor_lamaran' => $saved['nomor_lamaran'], 'institusi' => ' KAMPUS   BARU ',
        ]))->assertOk()->assertJsonPath('data.institusi', 'Kampus Baru');
        $this->assertSame(1, InstitusiPendidikan::count());
        $this->postJson('/api/lamaran/update', array_merge($payload, [
            'id' => $saved['id'], 'nomor_lamaran' => $saved['nomor_lamaran'], 'institusi' => '',
        ]))->assertOk()->assertJsonPath('data.institusi', null)->assertJsonPath('data.institusi_pendidikan_id', null);
    }
}
