<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Danestves\LaravelPolar\Billable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Projects a user may own without a paid subscription.
     */
    public const FREE_PROJECT_LIMIT = 3;

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

    /**
     * The schema-builder projects owned by the user.
     *
     * @return HasMany<SchemaProject, $this>
     */
    public function schemaProjects(): HasMany
    {
        return $this->hasMany(SchemaProject::class);
    }

    /**
     * The number of projects the user may own, or null when unlimited (paid).
     */
    public function projectLimit(): ?int
    {
        return $this->subscribed() ? null : self::FREE_PROJECT_LIMIT;
    }

    /**
     * Whether the user is allowed to create another schema project.
     */
    public function canCreateProject(): bool
    {
        if ($this->subscribed()) {
            return true;
        }

        return $this->schemaProjects()->count() < self::FREE_PROJECT_LIMIT;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
