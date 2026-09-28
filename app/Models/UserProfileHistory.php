<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfileHistory extends Model
{
    protected $table = 'user_profile_history';

    protected $fillable = [
        'changed_by', 'old_profile', 'new_profile', 'archived_fields', 'validation_notes',
    ];

    protected function casts(): array
    {
        return ['archived_fields' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}