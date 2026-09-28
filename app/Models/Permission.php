<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['key', 'label', 'description'];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(PermissionGroup::class, 'group_permission');
    }
}