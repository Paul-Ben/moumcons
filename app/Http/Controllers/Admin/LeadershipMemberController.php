<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Concerns\ContentRules;
use App\Models\LeadershipMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Admin → Leadership team shown on /about/leadership (PRD §9). */
class LeadershipMemberController extends Controller
{
    use ContentRules;

    public function index(): View
    {
        Gate::authorize('viewAny', LeadershipMember::class);

        return view('admin.leadership.index', ['members' => LeadershipMember::query()->ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', LeadershipMember::class);

        return view('admin.leadership.form', ['member' => new LeadershipMember(['is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', LeadershipMember::class);

        $member = LeadershipMember::create($this->validated($request));

        return redirect()->route('admin.leadership.index')->with('success', "{$member->name} added.");
    }

    public function edit(LeadershipMember $member): View
    {
        Gate::authorize('update', $member);

        return view('admin.leadership.form', ['member' => $member]);
    }

    public function update(Request $request, LeadershipMember $member): RedirectResponse
    {
        Gate::authorize('update', $member);

        $member->update($this->validated($request));

        return redirect()->route('admin.leadership.index')->with('success', "{$member->name} saved.");
    }

    public function destroy(LeadershipMember $member): RedirectResponse
    {
        Gate::authorize('delete', $member);

        $member->delete();

        return redirect()->route('admin.leadership.index')->with('success', "{$member->name} removed.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'photo' => $this->imageRules(),
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['sometimes', 'boolean'],
        ], $this->contentMessages());
        $data['sort_order'] ??= 0;

        return $data;
    }
}
