<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDomain extends Model
{
    protected $fillable = ['domain', 'active', 'is_default'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'is_default' => 'boolean'];
    }
}