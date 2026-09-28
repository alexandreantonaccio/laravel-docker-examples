<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermissionAdjustment extends Model
{
    protected $fillable = ['permission_id', 'effect', 'updated_by'];

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}