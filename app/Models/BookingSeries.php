<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingSeries extends Model
{
    protected $fillable = [
        'name', 'requester_user_id', 'teacher_id', 'environment_id',
        'booking_type_id', 'reason', 'weekdays', 'starts_on', 'ends_on',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return ['weekdays' => 'array', 'starts_on' => 'date', 'ends_on' => 'date'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
