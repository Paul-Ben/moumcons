<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

/** Media library (PRD §22/§23): view, upload/edit details, delete. */
class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-media');
    }

    public function create(User $user): bool
    {
        return $user->can('upload-media');
    }

    /** Alt text and caption are part of the upload, so they share its permission. */
    public function update(User $user, Media $media): bool
    {
        return $user->can('upload-media');
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->can('delete-media');
    }
}
