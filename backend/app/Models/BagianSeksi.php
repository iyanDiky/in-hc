<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;

class BagianSeksi extends Model
{
    use UuidV7, SoftDeletes, AuditTrail;

    protected $table = 'bagian_seksi';

    public const DELETED_AT = 'delete_at';

    protected $fillable = [
        'kode',
        'bagian_seksi',
        'unit_kerja_id',
    ];

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
}
