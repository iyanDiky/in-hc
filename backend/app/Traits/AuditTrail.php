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
            // Hardcode a dummy string according to the spec format for now
            return \Ramsey\Uuid\Uuid::uuid7()->toString() . ',' . \Ramsey\Uuid\Uuid::uuid7()->toString() . ',' . \Ramsey\Uuid\Uuid::uuid7()->toString();
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
