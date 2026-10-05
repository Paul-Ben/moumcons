<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessDivision;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Admin → FAQs (PRD §22), general or attached to a division page (§10). */
class FaqController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Faq::class);

        $division = $request->validate(['division' => ['nullable', 'string', 'max:20']])['division'] ?? null;

        $faqs = Faq::query()
            ->with('division:id,name')
            ->when($division === 'general', fn ($q) => $q->whereNull('business_division_id'))
            ->when(is_numeric($division), fn ($q) => $q->where('business_division_id', (int) $division))
            ->orderByRaw('business_division_id IS NOT NULL')
            ->orderBy('business_division_id')
            ->ordered()
            ->get();

        return view('admin.faqs.index', [
            'faqs' => $faqs,
            'division' => $division,
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id'),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Faq::class);

        return view('admin.faqs.form', $this->formData(new Faq([
            'is_published' => true,
            'business_division_id' => $request->integer('division') ?: null,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Faq::class);

        $data = $this->validated($request);
        // Without publish rights a new FAQ waits for an editor.
        $data['is_published'] ??= false;

        Faq::create($data);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added.');
    }

    public function edit(Faq $faq): View
    {
        Gate::authorize('update', $faq);

        return view('admin.faqs.form', $this->formData($faq));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        Gate::authorize('update', $faq);

        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ saved.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        Gate::authorize('delete', $faq);

        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        if ($request->has('is_published')) {
            Gate::authorize('publish', Faq::class);
        }

        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:20000'],
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $data['sort_order'] ??= 0;

        return $data;
    }

    /** @return array<string, mixed> */
    private function formData(Faq $faq): array
    {
        return [
            'faq' => $faq,
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            'categories' => Faq::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all(),
            'canPublish' => auth()->user()->can('publish', Faq::class),
        ];
    }
}
