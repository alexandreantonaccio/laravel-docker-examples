<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'requester_user_id', 'teacher_id', 'environment_id', 'booking_type_id',
        'booking_series_id', 'reason', 'booking_date', 'starts_at', 'ends_at',
        'status', 'decision_reason', 'cancellation_reason', 'teacher_other',
        'environment_other', 'booking_type_other',
    ];

    protected function casts(): array
    {
        return ['booking_date' => 'date'];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BookingType::class, 'booking_type_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(BookingSeries::class, 'booking_series_id');
    }
}
