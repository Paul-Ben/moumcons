{{-- Admin → Site settings (PRD §20/§22). Generated from App\Support\SiteSettings. --}}
@use('App\Http\Controllers\Admin\SettingsController')
@use('App\Support\SiteSettings')

<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6 max-w-4xl">
        @csrf
        @method('PUT')

        <x-admin.page-header title="Site Settings" description="Company details, social links and home page copy — no code changes needed." />

        @foreach ($groups as $groupKey => $group)
            <section class="card space-y-4" aria-labelledby="settings-{{ $groupKey }}">
                <div>
                    <h2 id="settings-{{ $groupKey }}" class="font-semibold text-moaum-charcoal">{{ $group['label'] }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $group['description'] }}</p>
                </div>

                @foreach ($group['fields'] as $key => $field)
                    @php
                        $name = SettingsController::inputName($key);
                        $value = $values[$key] ?? null;
                    @endphp

                    @switch($field['type'])
                        @case('textarea')
                            <x-admin.textarea :name="$name" :label="$field['label']" :value="$value" rows="3" :hint="$field['hint'] ?? null" />
                            @break

                        @case('image')
                            <x-admin.media-picker :name="$name" :label="$field['label']" :value="$value" :hint="$field['hint'] ?? null" />
                            @break

                        @case('stats')
                            @php
                                $rows = old($name, is_array($value) ? $value : []);
                            @endphp
                            <div x-data="{ rows: @js(array_values($rows)) }">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="form-label mb-0">{{ $field['label'] }}</span>
                                    <button type="button" class="btn-ghost text-sm border border-slate-200" x-show="rows.length < 4"
                                            @click="rows.push({ value: '', label: '', color: 'blue' })"><x-icon name="plus" class="w-4 h-4" /> Add</button>
                                </div>
                                @if ($field['hint'] ?? null)
                                    <p class="text-xs text-slate-400 mb-3">{{ $field['hint'] }}</p>
                                @endif
                                <template x-for="(row, index) in rows" :key="index">
                                    <div class="grid grid-cols-[6rem_1fr_7rem_auto] gap-2 mb-2">
                                        <input type="text" :name="`{{ $name }}[${index}][value]`" x-model="row.value" placeholder="16" maxlength="20" class="form-input h-10 text-sm" :aria-label="`Statistic ${index + 1} value`">
                                        <input type="text" :name="`{{ $name }}[${index}][label]`" x-model="row.label" placeholder="Business divisions" maxlength="60" class="form-input h-10 text-sm" :aria-label="`Statistic ${index + 1} label`">
                                        <select :name="`{{ $name }}[${index}][color]`" x-model="row.color" class="form-input h-10 text-sm" :aria-label="`Statistic ${index + 1} colour`">
                                            @foreach (SiteSettings::STAT_COLOURS as $colour => $colourLabel)
                                                <option value="{{ $colour }}">{{ $colourLabel }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-ghost p-2 text-moaum-red" @click="rows.splice(index, 1)" aria-label="Remove statistic"><x-icon name="trash" class="w-4 h-4" /></button>
                                    </div>
                                </template>
                                {{-- Submitting no rows must clear the list, so always send the key. --}}
                                <input type="hidden" name="{{ $name }}" value="" x-bind:disabled="rows.length > 0">
                                @error($name.'.*') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            @break

                        @default
                            <x-admin.input :name="$name" :type="$field['type']" :label="$field['label']" :value="$value" :hint="$field['hint'] ?? null" />
                    @endswitch
                @endforeach
            </section>
        @endforeach

        <div class="card sticky bottom-4 shadow-elevated">
            <x-admin.form-actions :cancel="route('admin.dashboard')" submit="Save settings" />
        </div>
    </form>
</x-layouts.admin>
