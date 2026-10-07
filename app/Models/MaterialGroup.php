<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialGroup extends Model
{
    protected $fillable = ['name', 'notification_emails', 'active'];

    protected function casts(): array
    {
        return ['notification_emails' => 'array', 'active' => 'boolean'];
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
