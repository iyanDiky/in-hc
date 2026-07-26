<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait UuidV7
{
    use HasUuids;

    /**
     * Generate a new UUID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return \Ramsey\Uuid\Uuid::uuid7()->toString();
    }
}
