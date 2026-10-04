<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * PRD §19 "Images should be optimized automatically" / §35 responsive images.
 *
 * Every upload is decoded and re-encoded as WebP, which also strips any
 * payload hidden in the original file (EXIF, polyglot content). Large images
 * are scaled down; a thumbnail is written for admin grids and cards.
 */
class MediaUploader
{
    public const MAX_WIDTH = 2000;

    public const THUMB_WIDTH = 480;

    private const QUALITY = 82;

    private readonly ImageManager $images;

    public function __construct()
    {
        $this->images = new ImageManager(new Driver);
    }

    public function store(UploadedFile $file, ?string $altText = null, string $disk = 'public'): Media
    {
        $image = $this->images->read($file->getRealPath());
        $image->scaleDown(width: self::MAX_WIDTH);

        $base = 'media/'.now()->format('Y/m').'/'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::lower(Str::random(6));
        $path = $base.'.webp';
        $thumbPath = $base.'-thumb.webp';

        $encoded = (string) $image->toWebp(self::QUALITY);
        Storage::disk($disk)->put($path, $encoded);

        $width = $image->width();
        $height = $image->height();

        $thumb = (string) $image->scaleDown(width: self::THUMB_WIDTH)->toWebp(self::QUALITY);
        Storage::disk($disk)->put($thumbPath, $thumb);

        return Media::create([
            'disk' => $disk,
            'path' => $path,
            'thumb_path' => $thumbPath,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime_type' => 'image/webp',
            'size' => strlen($encoded),
            'width' => $width,
            'height' => $height,
            'alt_text' => $altText,
            'uploaded_by' => Auth::id(),
        ]);
    }
}
