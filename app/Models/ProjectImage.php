<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** PRD §14/§19 — one image in a project's gallery (URL from the media library). */
class ProjectImage extends Model
{
    protected $fillable = ['project_id', 'image', 'caption', 'sort_order'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
