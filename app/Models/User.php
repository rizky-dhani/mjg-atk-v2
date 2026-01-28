<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'initial',
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
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->password)) {
                $user->password = Hash::make('Atk2025!');
            }
        });
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function isGA(): bool
    {
        return $this->divisions()->where('initial', 'GA')->exists() || $this->isSuperAdmin();
    }

    /**
     * @deprecated Use divisions() instead.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(UserDivision::class, 'division_user', 'user_id', 'division_id');
    }

    public function belongsToDivision(int|UserDivision $division): bool
    {
        $divisionId = $division instanceof UserDivision ? $division->id : $division;

        return $this->divisions()->where('user_divisions.id', $divisionId)->exists();
    }
}
