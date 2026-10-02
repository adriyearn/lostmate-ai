<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'student_id', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->profile()->create([]);
        });
    }

    /**
     * Send the "verify your email" message through the queue, so a slow or
     * broken mail server never makes registration fail.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * Treat everyone as verified when verification is switched off with
     * REQUIRE_EMAIL_VERIFICATION=false. Both the "verified" route middleware
     * and the registration email check this method, so this one switch
     * turns the whole feature on or off.
     */
    public function hasVerifiedEmail(): bool
    {
        if (! config('lostmate.require_email_verification')) {
            return true;
        }

        return parent::hasVerifiedEmail();
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function lostItems(): HasMany
    {
        return $this->hasMany(LostItem::class);
    }

    public function foundItems(): HasMany
    {
        return $this->hasMany(FoundItem::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'claimant_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversations()
    {
        return Conversation::where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
    }

    public function unreadMessagesCount(): int
    {
        return Message::whereIn('conversation_id', $this->conversations()->pluck('id'))
            ->where('sender_id', '!=', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    public function reportsFiled(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function adminLogs(): HasMany
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Up to two initials for the avatar circle, e.g. "Juan Dela Cruz" -> "JD".
     */
    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }
}
