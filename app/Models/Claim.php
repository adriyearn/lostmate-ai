<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Services\PhotoStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'found_item_id',
        'claimant_id',
        'lost_item_id',
        'ai_match_id',
        'identifying_details',
        'proof_image_path',
        'status',
        'finder_response',
        'reviewed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClaimStatus::class,
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * 6-digit code the claimant shows the finder at handover. The finder
     * must enter it before marking the item returned, which proves the
     * person collecting the item is the approved claimant.
     *
     * It is calculated, not stored: a keyed hash (HMAC) of the claim's id
     * using the app's secret APP_KEY. The same claim always gives the same
     * code, but nobody can work it out without the server's key.
     */
    public function pickupCode(): string
    {
        $hash = hash_hmac('sha256', 'claim-pickup:'.$this->id, (string) config('app.key'));

        // Turn the first 8 hex characters into a number, keep the last 6 digits.
        return str_pad((string) (hexdec(substr($hash, 0, 8)) % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /** Web address of the claimant's proof photo, or null. */
    protected function proofImageUrl(): Attribute
    {
        return Attribute::get(fn () => app(PhotoStorage::class)->url($this->proof_image_path));
    }

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(FoundItem::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function lostItem(): BelongsTo
    {
        return $this->belongsTo(LostItem::class);
    }

    public function aiMatch(): BelongsTo
    {
        return $this->belongsTo(AiMatch::class);
    }
}
