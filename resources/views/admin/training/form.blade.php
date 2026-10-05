{{-- Admin → create / edit a training programme (PRD §15). --}}
@php($editing = $programme->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$programme->title : 'New programme'">
    <form method="POST" action="{{ $editing ? route('admin.training.update', $programme) : route('admin.training.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $programme->title : 'New training programme'" :back="route('admin.training.index')" back-label="All programmes">
            @if ($editing && $programme->isPublished())
                <a href="{{ route('training.show', $programme) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="title" label="Programme title" :value="$programme->title" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$programme->slug" maxlength="255" hint="Leave blank to generate from the title." />
                    <x-admin.textarea name="summary" label="Summary" :value="$programme->summary" rows="2" maxlength="300" />
                    <x-admin.rich-text name="description" label="Description" :value="$programme->description" />
                </div>

                <div class="card space-y-4">
                    <x-admin.rich-text name="curriculum" label="Curriculum" :value="$programme->curriculum" hint="Modules or topics covered." />
                    <x-admin.rich-text name="requirements" label="Entry requirements" :value="$programme->requirements" />
                    <x-admin.textarea name="certificate_info" label="Certificate" :value="$programme->certificate_info" rows="2" maxlength="2000"
                                      hint="What participants receive on completion." />
                </div>

                <x-admin.seo-fields :model="$programme" />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    <x-admin.publish-fields :model="$programme" :can-publish="$canPublish" />
                    <x-admin.select name="status" label="Programme status" :options="$statuses" :value="$programme->status" required
                                    hint="Interest can be registered while Upcoming or Open for Registration." />
                </div>

                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Schedule &amp; delivery</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.input name="start_date" type="date" label="Start" :value="$programme->start_date?->format('Y-m-d')" />
                        <x-admin.input name="end_date" type="date" label="End" :value="$programme->end_date?->format('Y-m-d')" />
                    </div>
                    <x-admin.input name="registration_deadline" type="date" label="Registration deadline" :value="$programme->registration_deadline?->format('Y-m-d')" />
                    <x-admin.input name="duration" label="Duration" :value="$programme->duration" placeholder="e.g. 3 days, 6 weeks" />
                    <x-admin.select name="delivery_mode" label="Delivery mode" :options="$deliveryModes" :value="$programme->delivery_mode" required />
                    <x-admin.input name="venue" label="Venue" :value="$programme->venue" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.input name="fee" type="number" step="0.01" min="0" label="Fee (₦)" :value="$programme->fee" hint="Blank = on request; 0 = free." />
                        <x-admin.input name="capacity" type="number" min="1" label="Capacity" :value="$programme->capacity" />
                    </div>
                </div>

                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Classification</h2>
                    <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$programme->business_division_id" placeholder="— None —" />
                    <x-admin.input name="course_category" label="Course category" :value="$programme->course_category" list="course-categories" placeholder="e.g. Digital Skills" />
                    <datalist id="course-categories">
                        @foreach ($courseCategories as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>
                    <x-admin.input name="trainer" label="Trainer / facilitator" :value="$programme->trainer" />
                </div>

                <div class="card">
                    <x-admin.media-picker name="featured_image" label="Featured image" :value="$programme->featured_image" />
                </div>

                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.training.index')" :submit="$editing ? 'Save changes' : 'Create programme'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $programme)
            <div class="card mt-6 flex justify-end">
                <x-admin.delete-button :action="route('admin.training.destroy', $programme)" label="Delete programme" confirm="Delete this programme permanently?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
