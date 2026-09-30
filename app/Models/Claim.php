<?php

namespace App\Models;

use App\Enums\ClaimStatus;
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
