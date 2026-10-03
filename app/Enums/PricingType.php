<?php

namespace App\Enums;

/**
 * PRD §11 — pricing types. Internal pricing must not be exposed unless
 * the business manager approved it (starting_price is only rendered for
 * Fixed / StartingFrom).
 */
enum PricingType: string
{
    use TraitHasStatusLabels;

    case Fixed = 'fixed';
    case StartingFrom = 'starting_from';
    case QuoteRequired = 'quote_required';
    case ContactUs = 'contact_us';
    case NotPublished = 'not_published';

    /** Whether a stored starting_price may be shown publicly. */
    public function priceIsPublic(): bool
    {
        return in_array($this, [self::Fixed, self::StartingFrom], true);
    }

    /** Public display label for the pricing row (PRD §11 — never expose internal pricing). */
    public function priceLabel(): ?string
    {
        return match ($this) {
            self::Fixed => 'Fixed',
            self::StartingFrom => 'Starting From',
            self::QuoteRequired => 'Quote Required',
            self::ContactUs => 'Contact Us',
            self::NotPublished => null,   // no pricing shown at all
        };
    }
}
