<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Module 5 — "Key Capabilities" bullet items on the division detail page
 * (prototype-docs/business-detail.html). CMS-manageable in Module 9.
 */
class DivisionCapability extends Model
{
    use HasFactory;

    protected $fillable = ['business_division_id', 'title', 'description', 'sort_order'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(BusinessDivision::class, 'business_division_id');
    }
}
