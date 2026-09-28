<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfileOption extends Model
{
    protected $fillable = ['type', 'value', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}