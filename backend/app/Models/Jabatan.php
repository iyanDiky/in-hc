<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;

class Jabatan extends Model
{
    use UuidV7, SoftDeletes, AuditTrail;

    protected $table = 'jabatan';

    public const DELETED_AT = 'delete_at';

    protected $fillable = [
        'kode',
        'jabatan',
    ];
}
