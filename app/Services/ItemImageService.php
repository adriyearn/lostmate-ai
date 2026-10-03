<?php

namespace App\Services;

use App\Models\ItemImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class ItemImageService
{
    public const MAX_IMAGES = 3;

    public function __construct(protected PhotoStorage $photos) {}

    /**
     * Store the given uploaded files against an imageable model (LostItem or FoundItem).
     *
     * @param  UploadedFile[]  $files
     */
    public function store(Model $imageable, array $files): void
    {
        foreach ($files as $file) {
            $path = $this->photos->store($file, 'item-images');

            $imageable->images()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    public function delete(ItemImage $image): void
    {
        $this->photos->delete($image->path);
        $image->delete();
    }
}
