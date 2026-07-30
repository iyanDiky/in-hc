<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SuratMasukDisposisi extends Model
{
    use HasFactory, SoftDeletes;
    
    public const DELETED_AT = 'delete_at';

    protected $table = 'surat_masuk_disposisi';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // We handle our own timestamps as per the schema, but let's let Eloquent handle created_at / updated_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id',
        'surat_masuk_id',
        'disposisi_oleh',
        'disposisi_oleh_jabatan',
        'disposisi_oleh_bagian_seksi',
        'disposisi_waktu',
        'catatan',
        'evidence',
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
            if (empty($model->disposisi_waktu)) {
                $model->disposisi_waktu = now();
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
        // format: users_id,bagian_seksi_id,unit_kerja_id
        // In a real app we would get the auth user. Assuming auth()->user() is available.
        $user = auth()->user();
        if ($user) {
            $bagianSeksiId = $user->bagian_seksi_id ?? '';
            $unitKerjaId = $user->bagianSeksi->unit_kerja_id ?? '';
            return "{$user->id},{$bagianSeksiId},{$unitKerjaId}";
        }
        return null;
    }

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id', 'id');
    }

    public function disposisiOleh()
    {
        return $this->belongsTo(User::class, 'disposisi_oleh', 'id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'disposisi_oleh_jabatan', 'id');
    }

    public function bagianSeksi()
    {
        return $this->belongsTo(BagianSeksi::class, 'disposisi_oleh_bagian_seksi', 'id');
    }

    public function tujuans()
    {
        return $this->hasMany(SuratMasukDisposisiTujuan::class, 'surat_masuk_disposisi_id', 'id');
    }
}
