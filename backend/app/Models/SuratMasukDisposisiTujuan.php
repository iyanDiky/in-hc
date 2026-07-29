<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SuratMasukDisposisiTujuan extends Model
{
    use HasFactory;

    protected $table = 'surat_masuk_disposisi_tujuan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id',
        'surat_masuk_disposisi_id',
        'tujuan_disposisi',
        'created_by',
        'updated_by',
        'delete_at',
        'delete_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            $model->created_by = self::getAuditUser();
        });

        static::updating(function ($model) {
            $model->updated_by = self::getAuditUser();
        });

        static::deleting(function ($model) {
            $model->delete_by = self::getAuditUser();
            $model->delete_at = now();
            $model->save();
        });
    }

    private static function getAuditUser()
    {
        $user = auth()->user();
        if ($user) {
            $bagianSeksiId = $user->bagian_seksi_id ?? '';
            $unitKerjaId = $user->bagianSeksi->unit_kerja_id ?? '';
            return "{$user->id},{$bagianSeksiId},{$unitKerjaId}";
        }
        return null;
    }

    public function disposisi()
    {
        return $this->belongsTo(SuratMasukDisposisi::class, 'surat_masuk_disposisi_id', 'id');
    }

    public function tujuan()
    {
        return $this->belongsTo(BagianSeksi::class, 'tujuan_disposisi', 'id');
    }
}
