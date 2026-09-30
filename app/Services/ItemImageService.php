<?php

namespace App\Services;

use App\Models\ItemImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItemImageService
{
    public const MAX_IMAGES = 3;

    /**
     * Store the given uploaded files against an imageable model (LostItem or FoundItem).
     *
     * @param  UploadedFile[]  $files
     */
    public function store(Model $imageable, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('item-images', 'public');

            $imageable->images()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    public function delete(ItemImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }
}
