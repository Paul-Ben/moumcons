<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** PRD §19 — one photo in an album (URL from the media library). */
class GalleryImage extends Model
{
    protected $fillable = ['gallery_id', 'image', 'caption', 'sort_order'];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
