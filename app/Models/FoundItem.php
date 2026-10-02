<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Observers\FoundItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(FoundItemObserver::class)]
class FoundItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'item_name',
        'color',
        'brand',
        'description',
        'hidden_details',
        'location_found',
        'date_found',
        'time_found',
        'current_location',
        'status',
        'closed_at',
    ];

    // surrendered_at / surrendered_to are deliberately NOT fillable: only
    // OfficeCustodyService sets them, so a finder can't fake a drop-off.

    /**
     * Defense in depth: keeps hidden_details out of array/JSON
     * serialization (e.g. toJson(), an accidental response()->json($item))
     * even though nothing in the app currently serializes this model that
     * way. Direct property/Blade access ({{ $foundItem->hidden_details }})
     * is unaffected - authorization is still enforced by FoundItemPolicy.
     */
    protected $hidden = ['hidden_details'];

    protected function casts(): array
    {
        return [
            'date_found' => 'date',
            'status' => ItemStatus::class,
            'closed_at' => 'datetime',
            'surrendered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The office staff member (admin) who received the item. */
    public function surrenderedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surrendered_to');
    }

    /** True once the item has been turned in at the lost & found office. */
    public function isAtOffice(): bool
    {
        return $this->surrendered_at !== null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(ItemImage::class, 'imageable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function aiMatches(): HasMany
    {
        return $this->hasMany(AiMatch::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
