<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The one place that saves, shows, and deletes uploaded photos
 * (item photos, claim proof photos, and profile pictures).
 *
 * Two modes, picked automatically:
 *  - CLOUDINARY_URL is set (e.g. on Heroku): photos go to Cloudinary, a
 *    free image hosting service. Needed on hosts like Heroku, which erase
 *    files saved on the server on every deploy/restart.
 *  - Not set (your PC, and tests): photos go to storage/app/public like a
 *    normal Laravel app.
 *
 * What is saved in the database:
 *  - local:      "item-images/abc123.jpg"
 *  - Cloudinary: "cloudinary:lostmate/item-images/abc123"
 * The "cloudinary:" prefix tells url() and delete() where the photo lives.
 *
 * Cloudinary is called through its plain HTTP API with Laravel's Http
 * client - no extra package needed.
 */
class PhotoStorage
{
    protected const PREFIX = 'cloudinary:';

    /**
     * Save an uploaded photo and return the path to store in the database.
     */
    public function store(UploadedFile $file, string $folder): string
    {
        if (! $this->usesCloudinary()) {
            return $file->store($folder, 'public');
        }

        $params = ['folder' => 'lostmate/'.$folder, 'timestamp' => time()];

        $response = Http::asMultipart()
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->hashName())
            ->post($this->apiUrl('upload'), $this->signed($params));

        if ($response->failed() || ! $response->json('public_id')) {
            throw new RuntimeException('Photo upload to Cloudinary failed: '.$response->body());
        }

        return self::PREFIX.$response->json('public_id');
    }

    /**
     * Public web address of a photo, for <img src="...">.
     */
    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (! $this->isCloudinary($path)) {
            return asset('storage/'.$path);
        }

        // f_auto,q_auto: Cloudinary picks the best format and quality for
        // each browser, so photos load faster.
        return 'https://res.cloudinary.com/'.$this->credentials()['cloud'].'/image/upload/f_auto,q_auto/'.$this->publicId($path);
    }

    /**
     * Delete a photo. Never throws - a leftover file is better than a
     * failed request for the user.
     */
    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (! $this->isCloudinary($path)) {
            Storage::disk('public')->delete($path);

            return;
        }

        if ($this->usesCloudinary()) {
            Http::asForm()->post($this->apiUrl('destroy'), $this->signed([
                'public_id' => $this->publicId($path),
                'timestamp' => time(),
            ]));
        }
    }

    /**
     * The photo's raw bytes and type (used by AI photo matching), or null
     * if it can't be read or is larger than $maxBytes.
     *
     * @return array{bytes: string, mime: string}|null
     */
    public function read(string $path, int $maxBytes): ?array
    {
        if (! $this->isCloudinary($path)) {
            $disk = Storage::disk('public');

            if (! $disk->exists($path) || $disk->size($path) > $maxBytes) {
                return null;
            }

            return ['bytes' => $disk->get($path), 'mime' => $disk->mimeType($path) ?: 'image/jpeg'];
        }

        $response = Http::timeout(20)->get($this->url($path));

        if ($response->failed() || strlen($response->body()) > $maxBytes) {
            return null;
        }

        return ['bytes' => $response->body(), 'mime' => $response->header('Content-Type') ?: 'image/jpeg'];
    }

    public function usesCloudinary(): bool
    {
        return $this->credentials() !== null;
    }

    protected function isCloudinary(string $path): bool
    {
        return str_starts_with($path, self::PREFIX);
    }

    protected function publicId(string $path): string
    {
        return Str::after($path, self::PREFIX);
    }

    protected function apiUrl(string $action): string
    {
        return 'https://api.cloudinary.com/v1_1/'.$this->credentials()['cloud'].'/image/'.$action;
    }

    /**
     * Cloudinary proves a request comes from us with a "signature": a SHA-1
     * hash of the parameters (sorted by name) followed by our API secret.
     * The secret itself is never sent.
     */
    protected function signed(array $params): array
    {
        $credentials = $this->credentials();

        ksort($params);
        $toSign = collect($params)->map(fn ($value, $key) => "{$key}={$value}")->implode('&');

        return $params + [
            'api_key' => $credentials['key'],
            'signature' => sha1($toSign.$credentials['secret']),
        ];
    }

    /**
     * Reads CLOUDINARY_URL, which Cloudinary's dashboard gives you in the
     * form cloudinary://API_KEY:API_SECRET@CLOUD_NAME.
     *
     * @return array{key: string, secret: string, cloud: string}|null
     */
    protected function credentials(): ?array
    {
        $parts = parse_url((string) config('services.cloudinary.url'));

        if (($parts['scheme'] ?? null) !== 'cloudinary' || empty($parts['user']) || empty($parts['pass']) || empty($parts['host'])) {
            return null;
        }

        return ['key' => $parts['user'], 'secret' => $parts['pass'], 'cloud' => $parts['host']];
    }
}
