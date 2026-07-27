<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait AuditTrail
{
    /**
     * Boot the trait to observe model events.
     */
    public static function bootAuditTrail()
    {
        // Mocked auth user details for demonstration (users_id,bagian_seksi_id,unit_kerja_id)
        $getAuditUser = function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user) {
                // Return users_id,bagian_seksi_id,unit_kerja_id (unit_kerja_id from bagian_seksi usually, or if user has it)
                // Wait, User model doesn't have unit_kerja_id directly, but let's check its relation.
                // Assuming it has unit_kerja_id or we just put empty if not.
                $unitKerjaId = $user->unit_kerja_id ?? ($user->bagianSeksi->unit_kerja_id ?? '');
                return $user->id . ',' . $user->bagian_seksi_id . ',' . $unitKerjaId;
            }
            return 'system';
        };

        static::creating(function ($model) use ($getAuditUser) {
            $model->created_by = $getAuditUser();
        });

        static::updating(function ($model) use ($getAuditUser) {
            // When soft deleting, it might fire updating before deleting depending on Laravel version,
            // but we can check if it's dirty.
            if (! $model->isDirty('delete_at')) {
                $model->updated_by = $getAuditUser();
            }
        });

        static::deleting(function ($model) use ($getAuditUser) {
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model))) {
                $model->delete_by = $getAuditUser();
                // Because delete event handles the DB update differently, we might need to save it directly,
                // or let the soft delete mechanism save the updated model.
                $model->saveQuietly();
            }
        });
    }
}
