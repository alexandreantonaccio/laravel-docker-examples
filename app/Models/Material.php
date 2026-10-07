<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = ['material_group_id', 'name', 'code', 'description', 'quantity', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MaterialGroup::class, 'material_group_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(MaterialRental::class);
    }
}
