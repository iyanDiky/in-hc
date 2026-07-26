<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UuidV7, SoftDeletes, AuditTrail;

    public const DELETED_AT = 'delete_at';

    protected $fillable = [
        'npp',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jabatan_id',
        'bagian_seksi_id',
        'username',
        'password',
        'level',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function bagianSeksi()
    {
        return $this->belongsTo(BagianSeksi::class, 'bagian_seksi_id');
    }
}
