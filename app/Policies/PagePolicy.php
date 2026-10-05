<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** PRD §22/§23 — CMS pages. System pages back fixed routes and are never deleted. */
class PagePolicy extends ContentPolicy
{
    protected function area(): string
    {
        return 'pages';
    }

    public function delete(User $user, Model $model): bool
    {
        return ! $model->isSystem() && parent::delete($user, $model);
    }
}
