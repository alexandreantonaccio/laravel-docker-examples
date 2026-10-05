<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingType extends Model
{
    protected $fillable = ['name', 'color', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
