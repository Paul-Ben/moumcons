<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Media library item (PRD §19/§22). Created only through App\Services\MediaUploader,
 * which optimises the image and writes the thumbnail.
 */
class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'thumb_path', 'original_name', 'mime_type', 'size',
        'width', 'height', 'alt_text', 'caption', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Files go with the record; content that referenced the URL falls back
        // to its no-image state.
        static::deleted(function (self $media): void {
            Storage::disk($media->disk)->delete(array_filter([$media->path, $media->thumb_path]));
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) {
            return $q;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($term)).'%';

        return $q->where(fn (Builder $inner) => $inner
            ->where('original_name', 'like', $like)
            ->orWhere('alt_text', 'like', $like)
            ->orWhere('caption', 'like', $like));
    }

    /**
     * Site-relative URL stored by content records ("/storage/media/…"), so a
     * change of APP_URL or host never breaks saved content.
     */
    public function url(): string
    {
        return '/'.ltrim(parse_url(Storage::disk($this->disk)->url($this->path), PHP_URL_PATH), '/');
    }

    public function thumbUrl(): string
    {
        if ($this->thumb_path === null) {
            return $this->url();
        }

        return '/'.ltrim(parse_url(Storage::disk($this->disk)->url($this->thumb_path), PHP_URL_PATH), '/');
    }

    public function humanSize(): string
    {
        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 1).' MB'
            : number_format(max(1, $this->size / 1024)).' KB';
    }

    /** Shape handed to the admin media picker and Trix uploads. */
    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'thumb' => $this->thumbUrl(),
            'name' => $this->original_name,
            'alt' => $this->alt_text,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
