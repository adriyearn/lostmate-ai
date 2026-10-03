<?php

namespace App\Models;

use App\Services\PhotoStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'path',
        'original_name',
    ];

    /** Web address of the photo ($image->url), wherever it is stored. */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => app(PhotoStorage::class)->url($this->path));
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
