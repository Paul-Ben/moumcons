<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\View\View;

/** PRD §22 — public FAQs: general questions by topic, then per division. */
class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()
            ->published()
            ->with('division:id,name,slug,status')
            ->where(fn ($q) => $q->whereNull('business_division_id')
                ->orWhereHas('division', fn ($d) => $d->publiclyVisible()))
            ->ordered()
            ->get();

        [$general, $divisional] = $faqs->partition(fn (Faq $faq) => $faq->business_division_id === null);

        return view('public.faqs.index', [
            'general' => $general->groupBy(fn (Faq $faq) => $faq->category ?: 'General'),
            'divisional' => $divisional->groupBy(fn (Faq $faq) => $faq->division->name),
        ]);
    }
}
