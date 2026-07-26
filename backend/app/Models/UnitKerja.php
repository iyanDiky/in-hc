<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;

class UnitKerja extends Model
{
    use UuidV7, SoftDeletes, AuditTrail;

    protected $table = 'unit_kerja';

    public const DELETED_AT = 'delete_at';

    protected $fillable = [
        'kode',
        'unit_kerja',
    ];

    public function bagianSeksi()
    {
        return $this->hasMany(BagianSeksi::class, 'unit_kerja_id');
    }
}
