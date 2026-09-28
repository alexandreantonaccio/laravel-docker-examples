<?php

namespace App\Models;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'functional_id',
        'phone',
        'profile',
        'course',
        'job_title',
        'employment_link',
        'enrollment_proof_path',
        'registration_status',
        'is_active',
        'verification_sent_at',
        'documentation_validated_at',
        'documentation_validated_by',
        'documentation_notes',
        'rejected_at',
        'rejection_reason',
        'deactivated_at',
        'deactivated_by',
        'deactivation_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'verification_sent_at' => 'datetime',
            'documentation_validated_at' => 'datetime',
            'rejected_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(PermissionGroup::class, 'group_user');
    }

    public function permissionAdjustments(): HasMany
    {
        return $this->hasMany(UserPermissionAdjustment::class);
    }

    public function profileHistory(): HasMany
    {
        return $this->hasMany(UserProfileHistory::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(UserAuditLog::class);
    }

    public function hasPermissionTo(string $permission): bool
    {
        $permissionId = Permission::query()->where('key', $permission)->value('id');

        if (! $permissionId) {
            return false;
        }

        $adjustment = $this->permissionAdjustments()
            ->where('permission_id', $permissionId)
            ->value('effect');

        if ($adjustment === 'deny') {
            return false;
        }

        if ($adjustment === 'grant') {
            return true;
        }

        return $this->groups()
            ->whereHas('permissions', fn ($query) => $query->whereKey($permissionId))
            ->exists();
    }

    public function effectivePermissions(): array
    {
        $groupIds = $this->groups()->with('permissions:id,key')->get()
            ->flatMap(fn (PermissionGroup $group) => $group->permissions->pluck('id'));
        $adjustments = $this->permissionAdjustments()->with('permission:id,key')->get();
        $granted = $groupIds->merge($adjustments->where('effect', 'grant')->pluck('permission_id'));
        $denied = $adjustments->where('effect', 'deny')->pluck('permission_id');
        $effectiveIds = $granted->unique()->diff($denied);

        return Permission::query()->whereIn('id', $effectiveIds)->orderBy('key')->pluck('key')->all();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyUserEmail());
    }

    public function canLogin(): bool
    {
        return $this->is_active && $this->hasVerifiedEmail() && in_array($this->registration_status, [
            'email_confirmed', 'approved', 'rejected',
        ], true);
    }
}
