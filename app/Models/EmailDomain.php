<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDomain extends Model
{
    protected $fillable = ['domain', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}