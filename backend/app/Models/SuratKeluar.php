<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;
use Illuminate\Support\Facades\Auth;

class SuratKeluar extends Model
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

    public function userInput()
    {
        return $this->belongsTo(User::class, 'user_input');
    }

    public function userRequest()
    {
        return $this->belongsTo(User::class, 'user_request');
    }

    public function bagianSeksiRequest()
    {
        return $this->belongsTo(BagianSeksi::class, 'bagian_seksi_request');
    }
}
