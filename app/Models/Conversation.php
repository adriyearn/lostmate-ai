<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'lost_item_id',
        'found_item_id',
        'ai_match_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function lostItem(): BelongsTo
    {
        return $this->belongsTo(LostItem::class);
    }

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(FoundItem::class);
    }

    public function aiMatch(): BelongsTo
    {
        return $this->belongsTo(AiMatch::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function otherParticipant(User $user): User
    {
        return $this->user_one_id === $user->id ? $this->userTwo : $this->userOne;
    }

    public function hasParticipant(User $user): bool
    {
        return $this->user_one_id === $user->id || $this->user_two_id === $user->id;
    }

    /**
     * Unread messages in this conversation sent by the other participant.
     */
    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Find an existing conversation between two users about the same item
     * (regardless of who was user_one/user_two), or start a new one.
     */
    public static function findOrStartBetween(
        User $userA,
        User $userB,
        ?LostItem $lostItem = null,
        ?FoundItem $foundItem = null,
        ?AiMatch $aiMatch = null,
    ): self {
        $existing = static::query()
            ->where(function ($query) use ($userA, $userB) {
                $query->where('user_one_id', $userA->id)->where('user_two_id', $userB->id);
            })
            ->orWhere(function ($query) use ($userA, $userB) {
                $query->where('user_one_id', $userB->id)->where('user_two_id', $userA->id);
            })
            ->when($lostItem, fn ($q) => $q->where('lost_item_id', $lostItem->id), fn ($q) => $q->whereNull('lost_item_id'))
            ->when($foundItem, fn ($q) => $q->where('found_item_id', $foundItem->id), fn ($q) => $q->whereNull('found_item_id'))
            ->first();

        if ($existing) {
            return $existing;
        }

        return static::create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
            'lost_item_id' => $lostItem?->id,
            'found_item_id' => $foundItem?->id,
            'ai_match_id' => $aiMatch?->id,
            'last_message_at' => now(),
        ]);
    }
}
