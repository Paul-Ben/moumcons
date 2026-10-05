<?php

namespace App\Models;

use App\Enums\AccessLevel;
use App\Enums\DownloadCategory;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * PRD §18 — a file in the document library. Files live on the non-public
 * "documents" disk; App\Http\Controllers\Public\DownloadController checks the
 * access level and counts each download.
 */
class Document extends Model
{
    use HasFactory, HasUniqueSlug;

    public const DISK = 'documents';

    protected $fillable = [
        'business_division_id', 'title', 'slug', 'description', 'category',
        'access_level', 'file_path', 'original_name', 'mime_type', 'file_size',
        'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => DownloadCategory::class,
            'access_level' => AccessLevel::class,
            'is_published' => 'boolean',
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (self $document) => Storage::disk(self::DISK)->delete($document->file_path));
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** Documents a visitor may see listed: internal files only for staff with view-downloads. */
    public function scopeListableFor(Builder $q, ?Authenticatable $user): Builder
    {
        return $user?->can('view-downloads')
            ? $q
            : $q->where('access_level', '!=', AccessLevel::Internal);
    }

    public function canBeDownloadedBy(?Authenticatable $user): bool
    {
        return match ($this->access_level) {
            AccessLevel::Public => true,
            AccessLevel::Registered => $user !== null,
            AccessLevel::Internal => (bool) $user?->can('view-downloads'),
        };
    }

    /** Upper-case extension for badges, e.g. "PDF". */
    public function fileType(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'file');
    }

    public function humanSize(): string
    {
        return $this->file_size >= 1048576
            ? number_format($this->file_size / 1048576, 1).' MB'
            : number_format(max(1, $this->file_size / 1024)).' KB';
    }
}
