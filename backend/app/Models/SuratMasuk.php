<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;
use Illuminate\Support\Facades\Auth;

class SuratMasuk extends Model
{
    use HasFactory, UuidV7, SoftDeletes, AuditTrail;

    public const DELETED_AT = 'delete_at';

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->user_input && Auth::check()) {
                $model->user_input = Auth::id();
            }
        });
    }

    public function disposisi()
    {
        return $this->hasMany(SuratMasukDisposisi::class, 'surat_masuk_id', 'id');
    }

    public function userInput()
    {
        return $this->belongsTo(User::class, 'user_input');
    }
}
